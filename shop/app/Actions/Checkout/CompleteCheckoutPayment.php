<?php

declare(strict_types=1);

namespace App\Actions\Checkout;

use App\Enums\OrderStatusEnum;
use App\Enums\TransactionStatusEnum;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Transaction;

class CompleteCheckoutPayment
{
    public function __construct(
        private VerifyZarinpalPayment $verifyPayment,
        private DecrementInventoryAndMarkPaid $decrementAndMarkPaid,
    ) {}

    /**
     * Handle Zarinpal's callback redirect. Looks the transaction up by
     * `authority` (never by session — that's the only value guaranteed to
     * survive the round-trip). Returns the paid order on success, or null
     * for any failure path (Zarinpal rejected the payment, verify failed, or
     * inventory ran out in the meantime).
     */
    public function __invoke(string $authority, string $status): ?Order
    {
        $transaction = Transaction::query()
            ->where('transaction_id', $authority)
            ->with('order')
            ->first();

        if ($transaction === null || $transaction->order === null) {
            return null;
        }

        $order = $transaction->order;

        // Idempotency: a refreshed/duplicated callback for an already-paid
        // order must not re-verify or re-decrement inventory.
        if ($order->status === OrderStatusEnum::PAID) {
            return $order;
        }

        if ($status !== 'OK') {
            $this->fail($order, $transaction, TransactionStatusEnum::CANCELED, null, trans('messages.payment.canceled_by_user'));

            return null;
        }

        $verified = ($this->verifyPayment)($authority, $order->total_price);

        if ($verified === null || ! in_array($verified['code'], [100, 101], true)) {
            $this->fail($order, $transaction, TransactionStatusEnum::FAILED, $verified['code'] ?? null, trans('messages.payment.verify_failed'));

            return null;
        }

        if (! ($this->decrementAndMarkPaid)($order, $transaction, $verified['refId'])) {
            // Unlike the two paths above, Zarinpal already verified this
            // payment as successful (money genuinely captured) — this is the
            // rare race ORDER.md accepts (two customers reaching payment for
            // the last unit at once). Record ref_id/paid_at so staff can find
            // and manually refund it; a plain FAILED status with no ref_id
            // would hide that money needs to go back to the customer.
            $this->failPaidButOversold($order, $transaction, $verified['refId']);

            return null;
        }

        Cart::query()->where('user_id', $order->user_id)->delete();

        return $order->refresh();
    }

    private function fail(Order $order, Transaction $transaction, TransactionStatusEnum $status, ?int $resultCode, string $message): void
    {
        $order->update(['status' => OrderStatusEnum::CANCELED]);

        $transaction->update([
            'status' => $status,
            'result_code' => $resultCode === null ? null : (string) $resultCode,
            'result_message' => $message,
        ]);
    }

    private function failPaidButOversold(Order $order, Transaction $transaction, ?string $refId): void
    {
        $order->update(['status' => OrderStatusEnum::CANCELED]);

        $transaction->update([
            'status' => TransactionStatusEnum::FAILED,
            'ref_id' => $refId,
            'paid_at' => now(),
            'result_code' => '100',
            'result_message' => trans('messages.payment.paid_but_oversold'),
        ]);
    }
}
