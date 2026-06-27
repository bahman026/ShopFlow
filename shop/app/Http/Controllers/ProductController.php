<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Product\BuildProductBreadcrumbs;
use App\Actions\Product\BuildProductDetail;
use App\Actions\Product\GetRelatedProducts;
use App\Models\Product;
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
                'varieties' => fn ($query) => $query->published()->with([
                    'image',
                    'attribute.attributeGroup',
                    'attributes.attributeGroup',
                ]),
                'attributes',
                'reviews' => fn ($query) => $query->approved()->whereNull('parent_id')->with('user')->latest(),
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
