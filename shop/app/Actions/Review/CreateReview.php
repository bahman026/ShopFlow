<?php

declare(strict_types=1);

namespace App\Actions\Review;

use App\Enums\ReviewStatusEnum;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;

class CreateReview
{
    /**
     * Create a customer review for a product, always PENDING so it stays
     * hidden from the storefront until an admin approves it.
     *
     * @param  array{heading: string, content: string, rating: int}  $data
     */
    public function __invoke(User $user, Product $product, array $data): Review
    {
        return Review::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'heading' => $data['heading'],
            'content' => $data['content'],
            'rating' => $data['rating'],
            'status' => ReviewStatusEnum::PENDING,
        ]);
    }
}
