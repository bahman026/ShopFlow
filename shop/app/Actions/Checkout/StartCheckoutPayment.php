<?php

declare(strict_types=1);

namespace App\Actions\Checkout;

use App\DTOs\CartLineDTO;
use App\DTOs\CartSummaryDTO;
use App\DTOs\ShippingMethodDTO;
use App\Enums\OrderStatusEnum;
use App\Enums\TransactionPortEnum;
use App\Enums\TransactionStatusEnum;
use App\Models\Address;
use App\Models\User;
use Illuminate\Support\Collection;

class StartCheckoutPayment
{
    public function __construct(
        private CreatePendingOrder $createPendingOrder,
        private RequestZarinpalPayment $requestPayment,
    ) {}

    /**
     * Create the pending order + transaction, open a Zarinpal payment
     * session, and return the URL to send the customer to. Returns null if
     * Zarinpal couldn't be reached/rejected the request — the order and
     * transaction stay as a CANCELED/FAILED audit trail either way.
     *
     * @param  Collection<int, CartLineDTO>  $lines
     */
    public function __invoke(User $user, Collection $lines, Address $address, ShippingMethodDTO $method, CartSummaryDTO $summary, string $callbackUrl, string $ip): ?string
    {
        $order = ($this->createPendingOrder)($user, $lines, $address, $method, $summary);

        $transaction = $order->transactions()->create([
            'user_id' => $user->id,
            'status' => TransactionStatusEnum::PENDING,
            'port' => TransactionPortEnum::ZARINPAL,
            'amount' => $order->total_price,
            'ip' => $ip,
        ]);

        $result = ($this->requestPayment)(
            $order->total_price,
            'سفارش شماره '.$order->id,
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
