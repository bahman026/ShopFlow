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
