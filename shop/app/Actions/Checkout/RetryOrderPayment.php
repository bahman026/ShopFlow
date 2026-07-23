<?php

declare(strict_types=1);

namespace App\Actions\Checkout;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\OrderVariety;
use App\Models\User;
use App\Models\Variety;

class RetryOrderPayment
{
    public function __construct(private OpenZarinpalSession $openSession) {}

    /**
     * Retry payment for a canceled, never-charged order (Order::isRetryable)
     * without touching the cart: if every original line still has enough
     * live stock, reset the same order back to PENDING and open a fresh
     * Zarinpal session (a new Transaction row) for it. All-or-nothing — if
     * any line can no longer be fulfilled in full, nothing changes and
     * payment does not proceed. The order is reused rather than cloned so a
     * customer who cancels and retries repeatedly gets one order row with a
     * full attempt history in `transactions`, not a new order per attempt.
     */
    public function __invoke(Order $order, User $user, string $callbackUrl, string $ip): ?string
    {
        if (! $this->hasSufficientStock($order)) {
            return null;
        }

        $order->update(['status' => OrderStatusEnum::PENDING]);

        return ($this->openSession)($order, $user, $callbackUrl, $ip);
    }

    private function hasSufficientStock(Order $order): bool
    {
        $varietyIds = $order->orderVarieties->pluck('variety_id')->filter()->all();

        $varieties = Variety::query()->whereIn('id', $varietyIds)->get()->keyBy('id');

        return $order->orderVarieties->every(function (OrderVariety $line) use ($varieties): bool {
            $variety = $line->variety_id === null ? null : $varieties->get($line->variety_id);

            return $variety !== null && $variety->has_stock && $variety->inventory >= $line->quantity;
        });
    }
}
