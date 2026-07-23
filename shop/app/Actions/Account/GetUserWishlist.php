<?php

declare(strict_types=1);

namespace App\Actions\Account;

use App\Actions\Catalog\BuildProductCard;
use App\Models\User;
use App\Models\Wishlist;

class GetUserWishlist
{
    /**
     * Wishlist items shown per page in the account wishlist list.
     */
    private const PER_PAGE = 12;

    public function __construct(private BuildProductCard $buildProductCard) {}

    /**
     * Paginated, newest-first wishlist cards for one user's saved products.
     * Reuses BuildProductCard's lightweight card shape (used elsewhere for
     * category/brand/home listings), plus the product id so the account
     * page can submit the same wishlist-toggle route to remove it.
     *
     * @return array{data: array<int, array<string, mixed>>, meta: array{currentPage: int, lastPage: int, perPage: int, total: int, from: int|null, to: int|null}}
     */
    public function __invoke(User $user): array
    {
        $paginator = Wishlist::query()
            ->where('user_id', $user->id)
            ->with(['product.featuredImage', 'product.varieties.image'])
            // created_at alone isn't a reliable sort: rows saved within the
            // same second would tie with no deterministic order, so id
            // breaks it (same class of bug fixed in GetUserOrders).
            ->latest()
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        /** @var array<int, Wishlist> $items */
        $items = $paginator->items();

        return [
            'data' => array_map(fn (Wishlist $wishlist): array => ($this->buildProductCard)($wishlist->product), $items),
            'meta' => [
                'currentPage' => $paginator->currentPage(),
                'lastPage' => $paginator->lastPage(),
                'perPage' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ];
    }
}
