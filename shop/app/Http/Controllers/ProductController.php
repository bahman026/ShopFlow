<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Product\BuildProductBreadcrumbs;
use App\Actions\Product\BuildProductDetail;
use App\Actions\Product\GetRelatedProducts;
use App\Enums\ReviewStatusEnum;
use App\Enums\VarietyStatusEnum;
use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\Relation;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function show(
        string $slug,
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
        ]);
    }
}
