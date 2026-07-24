<?php

declare(strict_types=1);

namespace App\Actions\Review;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;

class FindProductBuyers
{
    /**
     * Order statuses that count as "bought it": payment succeeded and the
     * order wasn't later canceled or returned. Explicit list rather than
     * `>= PAID`, since CANCELED (60)/RETURNED (70) sort above DELIVERED (50).
     */
    private const PURCHASED_STATUSES = [
        OrderStatusEnum::PAID,
        OrderStatusEnum::PROCESSING,
        OrderStatusEnum::SHIPPED,
        OrderStatusEnum::DELIVERED,
    ];

    /**
     * Of the given candidate user ids, which have a purchased order containing
     * this product — used to flag "verified buyer" (خریدار) on their reviews.
     *
     * @param  array<int, int>  $candidateUserIds
     * @return array<int, int> the subset of user ids who bought the product
     */
    public function __invoke(int $productId, array $candidateUserIds): array
    {
        if ($candidateUserIds === []) {
            return [];
        }

        return Order::query()
            ->whereIn('user_id', $candidateUserIds)
            ->whereIn('status', self::PURCHASED_STATUSES)
            ->whereHas('orderVarieties', fn (Builder $query): Builder => $query->where('product_id', $productId))
            ->pluck('user_id')
            ->unique()
            ->values()
            ->all();
    }
}
