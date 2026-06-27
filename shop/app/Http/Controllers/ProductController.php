<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Cart\ResolveCartOwner;
use App\Actions\Product\BuildProductBreadcrumbs;
use App\Actions\Product\BuildProductDetail;
use App\Actions\Product\GetRelatedProducts;
use App\Enums\ReviewStatusEnum;
use App\Enums\VarietyStatusEnum;
use App\Models\Cart;
use App\Models\Product;
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
    ): Response {
        $product = Product::query()
            ->published()
            ->where('slug', $slug)
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
                'attributes',
                'reviews' => fn (Relation $query) => $query->where('status', ReviewStatusEnum::APPROVED->value)->whereNull('parent_id')->with('user')->latest(),
            ])
            ->firstOrFail();

        $product->increment('seen');

        return Inertia::render('Product/Show', [
            'product' => $buildProductDetail($product)->toArray(),
            'breadcrumbs' => $buildBreadcrumbs($product),
            'related' => $getRelatedProducts($product),
            'cartItems' => $this->cartItems($request, $resolveOwner, $product),
        ]);
    }

    /**
     * The current cart quantity (and line id) per variety of this product, so
     * the buy box can mirror the cart instead of a fresh add-to-cart button.
     *
     * @return array<int, array{id: int, count: int}>
     */
    private function cartItems(Request $request, ResolveCartOwner $resolveOwner, Product $product): array
    {
        $varietyIds = $product->varieties->pluck('id')->all();

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
