<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\Actions\Catalog\CalculatePricing;
use App\Actions\Catalog\TransformImage;
use App\Actions\Product\VarietyAttributes;
use App\DTOs\CartLineDTO;
use App\Models\Attribute;
use App\Models\Cart;
use App\Models\Variety;
use Illuminate\Support\Collection;

class GetCartLines
{
    public function __construct(
        private CalculatePricing $pricing,
        private TransformImage $transformImage,
        private VarietyAttributes $varietyAttributes,
    ) {}

    /**
     * The cart lines for an owner, newest first, shaped for the storefront.
     *
     * @param  array{user_id: int}|array{session_id: string}  $owner
     * @return Collection<int, CartLineDTO>
     */
    public function __invoke(array $owner): Collection
    {
        return Cart::query()
            ->where($owner)
            ->with([
                'variety.product.featuredImage',
                'variety.image',
                'variety.attribute.attributeGroup',
                'variety.attributes.attributeGroup',
            ])
            ->latest()
            ->get()
            ->map(fn (Cart $line): CartLineDTO => $this->line($line))
            ->values();
    }

    private function line(Cart $line): CartLineDTO
    {
        $variety = $line->variety;
        $product = $variety->product;
        $pricing = $this->pricing->forVariety($variety);

        $image = $this->transformImage->__invoke(
            $variety->image ?? $product->featuredImage,
        );

        return new CartLineDTO(
            id: $line->id,
            varietyId: $variety->id,
            heading: $product->heading,
            url: '/products/'.$product->slug,
            image: $image,
            color: $variety->color,
            attributes: $this->attributes($variety),
            unitPrice: $pricing['salePrice'] ?? $pricing['price'],
            originalPrice: $pricing['price'],
            discountPercent: $pricing['discountPercent'],
            count: $line->count,
            inventory: $variety->inventory,
            inStock: $variety->has_stock && $variety->inventory > 0,
        );
    }

    /**
     * @return array<int, array{group: string|null, value: string}>
     */
    private function attributes(Variety $variety): array
    {
        return collect($this->varietyAttributes->__invoke($variety))
            ->map(fn (Attribute $attribute): array => [
                'group' => $attribute->attributeGroup?->name,
                'value' => $attribute->value,
            ])
            ->all();
    }
}
