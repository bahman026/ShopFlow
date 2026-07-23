<?php

declare(strict_types=1);

namespace App\Actions\Checkout;

use App\DTOs\CartLineDTO;
use App\DTOs\CartSummaryDTO;
use App\DTOs\ShippingMethodDTO;
use App\Models\Address;
use App\Models\User;
use Illuminate\Support\Collection;

class StartCheckoutPayment
{
    public function __construct(
        private CreatePendingOrder $createPendingOrder,
        private OpenZarinpalSession $openSession,
    ) {}

    /**
     * Create the pending order, open a Zarinpal payment session, and return
     * the URL to send the customer to. Returns null if Zarinpal couldn't be
     * reached/rejected the request — the order and transaction stay as a
     * CANCELED/FAILED audit trail either way.
     *
     * @param  Collection<int, CartLineDTO>  $lines
     */
    public function __invoke(User $user, Collection $lines, Address $address, ShippingMethodDTO $method, CartSummaryDTO $summary, string $callbackUrl, string $ip): ?string
    {
        $order = ($this->createPendingOrder)($user, $lines, $address, $method, $summary);

        return ($this->openSession)($order, $user, $callbackUrl, $ip);
    }
}
