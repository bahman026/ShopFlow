# Orders & Inventory

Strategy A (below) is implemented: order creation is `App\Actions\Checkout\CreatePendingOrder` (shop), the row-locked decrement is `App\Actions\Checkout\DecrementInventoryAndMarkPaid`, wired together by `App\Actions\Checkout\CompleteCheckoutPayment` (Zarinpal callback handler, `PaymentController@callback`). Strategy B is still just notes.

## When does `varieties.inventory` decrease?

After successful payment. Not when adding to cart, and not when entering the payment page.

- Add to cart: no change to `inventory`. A cart is only an intention.
- Go to payment: no change. The order is created with a pending status.
- Payment success (gateway callback): decrement `inventory -= quantity` per line and mark the order paid.
- Payment fails, cancels, or the user disappears: `inventory` was never touched, so nothing is given back.

## Chosen approach: Strategy A (decrement on paid)

Inventory never changes until payment is confirmed. Cart and checkout are inventory-neutral; the paid callback is the only place stock goes down.

### The rule that keeps it safe

Do the final check and decrement in one DB transaction with a row lock on the variety:

1. `SELECT ... FOR UPDATE` the variety row.
2. Verify `inventory >= quantity`.
3. Decrement and save.
4. Commit (all-or-nothing).

The lock stops two simultaneous payments from both selling the last unit, so no overselling.

### Source of truth

`orders.status` (pending vs paid) decides everything. Pending = no stock change. Paid = stock decremented. Nothing to reconcile or release.

### Trade-off

The last unit is not held during payment, so two people can both reach the payment page and the second fails at the final confirm. Rare except on hot or flash-sale items.

`PaymentController::initiate()` calls `ValidateCartStock` to re-check live inventory right before opening a Zarinpal payment session — this closes the common, fully-preventable case (item already out of stock when the customer clicks پرداخت) so nobody is charged for something unavailable. It cannot close the race above (two people mid-payment for the same last unit); that residual case still reaches `DecrementInventoryAndMarkPaid`'s rejection, and since Zarinpal's verify already succeeded there (money genuinely captured), `CompleteCheckoutPayment::failPaidButOversold()` keeps `ref_id`/`paid_at` and writes a "needs manual refund" message into `result_message` instead of a plain `FAILED` with no trace — check the Transactions table for `result_message` mentioning بازگشت وجه.

### Known quirk: `AddToCart`/`MergeGuestCart` floor quantity at 1 even when inventory is 0

Both `App\Actions\Cart\AddToCart` and `App\Actions\Cart\MergeGuestCart` clamp with `max(1, min($desired, $variety->inventory))` — if `$variety->inventory` is 0, this still forces the cart line's `count` to 1. It's harmless at the two call sites that already exist (`CartController::store()` rejects a zero-inventory variety before ever calling `AddToCart`; `MergeGuestCart` only hits zero inventory in the rare case an item went out of stock while sitting in a guest cart, and `GetCartLines`' own `inStock` check still correctly hides it as unavailable everywhere it's displayed). If you add another `AddToCart` call site, check `has_stock`/`inventory` yourself first rather than relying on the floor.

## Retrying payment on a canceled order ("پرداخت مجدد")

`Order::isRetryable()` allows retry only for a `CANCELED` order whose latest transaction never actually captured money (customer backed out at Zarinpal, or verification failed) — never for the oversold case above, where Zarinpal already captured payment and a retry would risk double-charging before a manual refund.

`App\Actions\Checkout\RetryOrderPayment` (used by `AccountController::retryOrder()`) pays directly — it does **not** touch the cart or redirect the customer back through checkout:

1. Re-check live stock for every original line (`has_stock` and `inventory >= quantity`), all-or-nothing. If any line can no longer be fulfilled in full, nothing changes and the customer is bounced back to the order's own page with a message — no partial retry.
2. If stock is sufficient, reset the **same** order back to `PENDING` (its `order_varieties`/address/shipping/totals are untouched) rather than cloning a new order row.
3. Open a Zarinpal session for that order via `App\Actions\Checkout\OpenZarinpalSession` (the same action `StartCheckoutPayment` uses for a normal checkout) and redirect to Zarinpal's StartPay URL. This adds a new `Transaction` row for the order — it never reuses an old one — so a customer who cancels and retries repeatedly ends up with one order and several transactions (a full attempt history), not a new order per attempt.

Because each attempt gets its own Zarinpal authority (a new `Transaction` row), the existing `checkout.callback` route and `CompleteCheckoutPayment` need no special-casing — the callback looks up by authority and resolves to the (same) order either way. `Order::isRetryable()`/`BuildOrderDTO` pick the latest transaction by `id` (not insertion order or a plain `created_at` sort) since several transactions can now share the same order and even the same `created_at` second.

**Known tradeoff**: since retries reuse the order, an old *stale* Zarinpal callback URL (e.g. the customer navigates back to a previous attempt's return link after already starting a newer one) could re-verify against the wrong, now-superseded authority and cancel an order with a different attempt genuinely in flight. This is the same class of rare, accepted race as the last-unit-oversell case below — not specifically guarded against.

## Returned orders (`RETURNED`): nothing happens automatically

Setting an order's `status` to `RETURNED` (in the Filament admin panel — there's no storefront-side path to it) is a plain data write. Nothing else is triggered:

- **Inventory is not restocked.** No code anywhere increments `varieties.inventory` back; Strategy A only ever decrements on payment and never reverses it for a return.
- **No `Receipt` or `Transaction` row is created or updated.** There's no observer/event tied to `orders.status` — changing it doesn't touch either table.
- **No refund is tracked.** Neither `receipts` nor `transactions` has a refund-related column (`refunded_at`, `refund_amount`, etc.) — the free-text "بازگشت وجه" note in `Transaction.result_message` is written only for the unrelated oversold-payment race (`failPaidButOversold()` above), not for a manual return.
- **No status-transition validation.** `status` is a plain Filament `Select`; any status can be set to `RETURNED` from any prior status.

Until this is built, staff must handle a return manually: restock the variety's inventory if the item is resellable, and record/process the refund outside the system (there's currently nowhere in the schema to note it other than a free-text `order_notes` entry).

## Planned: expiring abandoned PENDING orders after 15 minutes

**Not yet implemented** — documenting the decision now so the eventual build matches it.

A customer who opens a Zarinpal payment session and then abandons it (closes the tab, never returns) leaves the order stuck as `PENDING` forever — `checkout.callback` is the only thing that ever changes its status, and it's never called if the customer doesn't come back. Since Strategy A never touches inventory for a `PENDING` order, this doesn't oversell anything, but it clutters the order list (admin panel and the customer's own `/account/orders`) with stale, never-resolved rows.

Decision: a scheduled job should mark any `PENDING` order **`CANCELED`** once it's more than 15 minutes old with no successful payment — same as any other canceled order (a normal `fail()`-style status flip, row kept as-is, `order_varieties` untouched). Not a hard delete — deleting would break the audit-trail invariant every other cancel path in this codebase relies on (oversold tracking, retry history, admin visibility). An expired order becomes retryable through the existing `Order::isRetryable()` / `RetryOrderPayment` flow like any other canceled order, no special-casing needed there.

Open question for whoever builds this: what "15 minutes old" should measure — `orders.created_at`, or the latest `Transaction.created_at` (so a retry restarts the clock rather than the job racing to expire an order the customer just retried a few seconds before minute 15). The latter matches the reused-order retry design in the section above and is the more correct choice.

## If this is not enough later: Strategy B (reserve at checkout)

Only consider this if real lost sales, oversell complaints, or flash sales appear. It is an additive change, so deferring it costs nothing now.

- Hold stock when the user enters payment, with an `expires_at` (e.g. 15 min).
- Available to sell = `inventory - active reservations`.
- Payment success: convert the hold to a real decrement.
- Payment fail/cancel: release immediately.
- Timeout: a scheduled job releases expired holds.
- Same transaction + `SELECT ... FOR UPDATE` rule applies when reserving.

Preferred shape if B is needed: a `reservations` table (`variety_id`, `quantity`, `user_id`/`session_id`, `order_id`, `expires_at`, `status`) rather than a bare `reserved` counter, because it is auditable and easier to expire correctly.

## Payments: receipts vs transactions/gateways

Two payment paths, kept separate — **a paid order only ever has a row in one of them, never both**:

- Manual / offline payments use `receipts` (admin table built; not yet wired into the storefront checkout flow): card-to-card, Paya transfers, prepayments. The customer provides a tracking code or uploads a receipt image, and staff confirm it. Fields: `destination_bank`, `end_of_card_number`, `tracking_code`, `is_paya`, plus a polymorphic receipt image.
- Online gateway payments use `transactions` (built, storefront-side): **Zarinpal only so far, sandbox mode** (`port = ZARINPAL`). Mellat and Parsian are not built. The shop reads Zarinpal's `merchant_id`/base URL from `config('services.zarinpal.*')`/`.env`, not the admin `gateways` table (nothing is seeded there yet) — revisit once a second gateway needs real *selection* logic (`gateways.active`/`priority`). See `AGENTS.md` → "Order creation + Zarinpal payment" for the full flow.

**A Zarinpal-paid order will never show a `receipts` row** — nothing in the codebase creates one for an online gateway payment (no observer/event links `Transaction` to `Receipt`); checking the admin panel's Order → Receipts tab and finding it empty for a Zarinpal order is expected, not a bug. `receipts` only gets rows from the (not-yet-built) manual bank-transfer flow.

Keep `receipts` if there is any chance of manual bank transfers (typical for Iranian shops). If the shop ever becomes gateway-only, `transactions`/`gateways` would cover everything and `receipts` could be retired.
