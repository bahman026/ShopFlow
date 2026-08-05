<?php

declare(strict_types=1);

namespace App\Actions\Coupon;

use App\DTOs\CartLineDTO;
use App\Models\Category;
use App\Models\Coupon;
use Illuminate\Support\Collection;

class CalculateCouponDiscount
{
    /**
     * What a coupon takes off the cart, in Toman.
     *
     * Only the eligible lines count: a coupon scoped to products, varieties or
     * categories discounts just those lines, while an unscoped coupon applies
     * to the whole cart. Percentages are computed on the eligible total after
     * variety sale prices, then capped by `max_discount`, and the result can
     * never exceed what those lines are actually worth.
     *
     * @param  Collection<int, CartLineDTO>  $lines
     */
    public function __invoke(Coupon $coupon, Collection $lines): int
    {
        $eligible = $this->eligibleTotal($coupon, $lines);

        if ($eligible <= 0) {
            return 0;
        }

        $discount = $coupon->is_percent
            ? (int) round($eligible * $coupon->amount / 100)
            : $coupon->amount;

        if ($coupon->max_discount !== null && $coupon->max_discount > 0) {
            $discount = min($discount, $coupon->max_discount);
        }

        return max(0, min($discount, $eligible));
    }

    /**
     * Worth of the cart lines a coupon is allowed to discount.
     *
     * @param  Collection<int, CartLineDTO>  $lines
     */
    public function eligibleTotal(Coupon $coupon, Collection $lines): int
    {
        return (int) $lines
            ->filter(fn (CartLineDTO $line): bool => $this->covers($coupon, $line))
            ->sum(fn (CartLineDTO $line): int => $line->lineTotal());
    }

    private function covers(Coupon $coupon, CartLineDTO $line): bool
    {
        if (! $coupon->isScoped()) {
            return true;
        }

        if ($coupon->varieties->contains('id', $line->varietyId)) {
            return true;
        }

        if ($coupon->products->contains('id', $line->productId)) {
            return true;
        }

        return $line->categoryId !== null
            && in_array($line->categoryId, $this->categoryIds($coupon), true);
    }

    /**
     * The coupon's categories plus their descendants, so a coupon on a parent
     * category also covers products filed under its sub-categories (the same
     * rule catalog pages use).
     *
     * @return array<int, int>
     */
    private function categoryIds(Coupon $coupon): array
    {
        $ids = $coupon->categories->pluck('id')->map(fn (int $id): int => $id)->all();

        if ($ids === []) {
            return [];
        }

        $frontier = $ids;

        while (true) {
            /** @var array<int, int> $children */
            $children = Category::query()
                ->whereIn('parent_id', $frontier)
                ->pluck('id')
                ->map(fn (int $id): int => $id)
                ->all();

            $children = array_values(array_diff($children, $ids));

            if ($children === []) {
                return $ids;
            }

            $ids = array_merge($ids, $children);
            $frontier = $children;
        }
    }
}
