<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Image;
use App\Models\Product;
use App\Models\Variety;
use App\Observers\Concerns\ForgetsProductCache;

/**
 * Drops a product's cached payload when one of its images changes.
 *
 * `images` is polymorphic, so this fires for banners, brands and menu items
 * too; only the product- and variety-owned rows resolve to a product and
 * anything else returns early.
 *
 * Variety images count: a product card falls back to the first variety image
 * when the product itself has none (`BuildProductCard::cardImage`), so a
 * variety photo can be what a whole category listing renders.
 *
 * Note `Image::booted()` demotes sibling `is_featured` rows with a query-builder
 * `update()`, which fires no events for those siblings. That needs no extra
 * handling: the row being promoted *does* arrive here, and it belongs to the
 * same product, so the page is refreshed either way.
 */
class ImageObserver
{
    use ForgetsProductCache;

    public function saved(Image $image): void
    {
        $this->invalidate($image);
    }

    public function updated(Image $image): void
    {
        $this->invalidate($image);
    }

    public function deleted(Image $image): void
    {
        $this->invalidate($image);
    }

    private function invalidate(Image $image): void
    {
        $this->forgetProducts([$this->productId($image)], affectsCards: true);
    }

    /**
     * The product this image ultimately belongs to, or null when it belongs to
     * something outside the catalog.
     */
    private function productId(Image $image): ?int
    {
        return match ($image->imageable_type) {
            Product::class => $image->imageable_id,
            Variety::class => $this->productIdOfVariety($image->imageable_id),
            default => null,
        };
    }

    private function productIdOfVariety(int $varietyId): ?int
    {
        $productId = Variety::query()->whereKey($varietyId)->value('product_id');

        return is_numeric($productId) ? (int) $productId : null;
    }
}
