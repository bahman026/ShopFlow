<?php

declare(strict_types=1);

namespace App\Actions\Checkout;

use App\Enums\OrderStatusEnum;
use App\Enums\TransactionPortEnum;
use App\Enums\TransactionStatusEnum;
use App\Models\Order;
use App\Models\User;

class OpenZarinpalSession
{
    public function __construct(private RequestZarinpalPayment $requestPayment) {}

    /**
     * Create a PENDING transaction for an already-created PENDING order and
     * open a Zarinpal payment session for it. Returns the StartPay URL to
     * redirect the customer to, or null if Zarinpal couldn't be reached/
     * rejected the request — the order and transaction stay as a
     * CANCELED/FAILED audit trail either way. Shared by the normal checkout
     * flow (StartCheckoutPayment) and order-retry (RetryOrderPayment).
     */
    public function __invoke(Order $order, User $user, string $callbackUrl, string $ip): ?string
    {
        $transaction = $order->transactions()->create([
            'user_id' => $user->id,
            'status' => TransactionStatusEnum::PENDING,
            'port' => TransactionPortEnum::ZARINPAL,
            'amount' => $order->total_price,
            'ip' => $ip,
        ]);

        $result = ($this->requestPayment)(
            $order->total_price,
            trans('messages.payment.order_number', ['id' => $order->id]),
            $callbackUrl,
            $user->hasPlaceholderEmail() ? null : $user->email,
            $user->mobile,
        );

        if ($result === null) {
            $transaction->update(['status' => TransactionStatusEnum::FAILED]);
            $order->update(['status' => OrderStatusEnum::CANCELED]);

            return null;
        }

        $transaction->update(['transaction_id' => $result['authority']]);

        return $this->requestPayment->startPayUrl($result['authority']);
    }
}
