# Orders & Inventory

Notes for when the orders and payments flow is built. Not implemented yet.

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

## If this is not enough later: Strategy B (reserve at checkout)

Only consider this if real lost sales, oversell complaints, or flash sales appear. It is an additive change, so deferring it costs nothing now.

- Hold stock when the user enters payment, with an `expires_at` (e.g. 15 min).
- Available to sell = `inventory - active reservations`.
- Payment success: convert the hold to a real decrement.
- Payment fail/cancel: release immediately.
- Timeout: a scheduled job releases expired holds.
- Same transaction + `SELECT ... FOR UPDATE` rule applies when reserving.

Preferred shape if B is needed: a `reservations` table (`variety_id`, `quantity`, `user_id`/`session_id`, `order_id`, `expires_at`, `status`) rather than a bare `reserved` counter, because it is auditable and easier to expire correctly.
