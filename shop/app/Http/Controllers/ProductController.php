<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Cart\ResolveCartOwner;
use App\Actions\Layout\GetSliderByPosition;
use App\Actions\Product\BuildProductBreadcrumbs;
use App\Actions\Product\BuildProductDetail;
use App\Actions\Product\GetRelatedProducts;
use App\DTOs\VarietyDTO;
use App\Enums\ReviewStatusEnum;
use App\Enums\SliderPositionEnum;
use App\Enums\VarietyStatusEnum;
use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use App\Support\ProductCache;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function show(
        string $slug,
        Request $request,
        ResolveCartOwner $resolveOwner,
        BuildProductDetail $buildProductDetail,
        BuildProductBreadcrumbs $buildBreadcrumbs,
        GetRelatedProducts $getRelatedProducts,
        GetSliderByPosition $getSliderByPosition,
    ): Response {
        // The bare row, deliberately without the page's eager loads: it answers
        // the 404, carries the view counter and keys the per-visitor props
        // below. The expensive half of the page is cached, so on a cache hit
        // this is the only catalog query the request runs — which is the whole
        // point of splitting it out.
        $product = Product::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $product->increment('seen');

        $detail = ProductCache::rememberDetail(
            $slug,
            fn (): array => $this->buildDetail($product->id, $buildProductDetail, $buildBreadcrumbs),
        );

        return Inertia::render('Product/Show', [
            'product' => $detail['product'],
            'breadcrumbs' => $detail['breadcrumbs'],
            // Cards of *other* products, so this is a list entry rather than
            // part of the product's own payload: any product write refreshes
            // it, not only a write to this one.
            'related' => ProductCache::rememberList(
                'related',
                ['product' => $product->id],
                fn (): array => $getRelatedProducts($product),
            ),
            'cartItems' => $this->cartItems($request, $resolveOwner, $detail['varietyIds']),
            'isWishlisted' => $this->isWishlisted($request, $product),
            // Any logged-in user may review; the form is hidden for guests.
            'canReview' => $request->user() instanceof User,
            // Shared across every product; empty until an admin assigns a
            // published slider to the position.
            'sideSlides' => $getSliderByPosition(SliderPositionEnum::PRODUCT_SIDE),
        ]);
    }

    /**
     * The cacheable half of the page: everything derived purely from the
     * product, with no per-visitor or per-request input mixed in.
     *
     * Note the payload carries variety prices and **stock counts**, which a
     * purchase makes wrong. That is safe because every observer that writes a
     * variety clears this entry (`App\Observers\VarietyObserver`), and because
     * nothing here is trusted when money moves: `CartController`,
     * `ValidateCartStock` and the row-locked decrement each re-read live
     * inventory (see `docs/ORDER.md`). Worst case a customer sees a stale
     * quantity cap for the width of a race and is corrected server-side.
     *
     * `varietyIds` rides along so a cache hit can still look up this visitor's
     * cart lines without re-loading the varieties it just read from cache.
     *
     * @return array{product: array<string, mixed>, breadcrumbs: array<int, array{heading: string, url: string}>, varietyIds: array<int, int>}
     */
    private function buildDetail(
        int $productId,
        BuildProductDetail $buildProductDetail,
        BuildProductBreadcrumbs $buildBreadcrumbs,
    ): array {
        $product = Product::query()
            ->published()
            ->whereKey($productId)
            ->with([
                'featuredImage',
                'images',
                'brand',
                'category',
                'varieties' => fn (Relation $query) => $query->where('status', VarietyStatusEnum::PUBLISHED->value)->with([
                    'image',
                    'attribute.attributeGroup',
                    'attributes.attributeGroup',
                ]),
                'attributes.attributeGroup',
                'reviews' => fn (Relation $query) => $query->where('status', ReviewStatusEnum::APPROVED->value)->whereNull('parent_id')->with('user')->latest(),
            ])
            ->firstOrFail();

        $detail = $buildProductDetail($product);

        return [
            'product' => $detail->toArray(),
            'breadcrumbs' => $buildBreadcrumbs($product),
            'varietyIds' => array_map(fn (VarietyDTO $variety): int => $variety->id, $detail->varieties),
        ];
    }

    /**
     * Whether the current user has saved this product, so the buy box can
     * show a filled/outlined heart instead of a fresh wishlist button.
     * Guests (no session-based wishlist support, unlike cart) always see false.
     */
    private function isWishlisted(Request $request, Product $product): bool
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return false;
        }

        return Wishlist::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->exists();
    }

    /**
     * The current cart quantity (and line id) per variety of this product, so
     * the buy box can mirror the cart instead of a fresh add-to-cart button.
     *
     * Never cached: it is per visitor, and it is read straight after the cached
     * payload precisely so the cart state is always live.
     *
     * @param  array<int, int>  $varietyIds
     * @return array<int, array{id: int, count: int}>
     */
    private function cartItems(Request $request, ResolveCartOwner $resolveOwner, array $varietyIds): array
    {
        if ($varietyIds === []) {
            return [];
        }

        return Cart::query()
            ->where($resolveOwner($request))
            ->whereIn('variety_id', $varietyIds)
            ->get(['id', 'variety_id', 'count'])
            ->mapWithKeys(fn (Cart $line): array => [
                $line->variety_id => ['id' => $line->id, 'count' => $line->count],
            ])
            ->all();
    }
}
