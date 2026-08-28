<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Review;
use App\Observers\Concerns\ForgetsProductCache;

/**
 * Drops a product's cached page when one of its reviews changes.
 *
 * The cached payload carries the approved reviews, their count and the average
 * rating, so the write that matters is an admin approving a pending review:
 * without this the new review stays invisible for the rest of the entry's TTL,
 * which reads exactly like the moderation not having worked.
 *
 * `affectsCards: false` throughout — no product card shows a rating.
 */
class ReviewObserver
{
    use ForgetsProductCache;

    public function saved(Review $review): void
    {
        $this->forgetProducts([$review->product_id], affectsCards: false);
    }

    public function updated(Review $review): void
    {
        $this->forgetProducts([$review->product_id], affectsCards: false);
    }

    public function deleted(Review $review): void
    {
        $this->forgetProducts([$review->product_id], affectsCards: false);
    }
}
