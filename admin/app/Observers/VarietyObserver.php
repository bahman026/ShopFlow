<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Variety;
use App\Observers\Concerns\ForgetsProductCache;

/**
 * Drops a product's cached payload when one of its varieties changes.
 *
 * This is the observer that matters most, and the reason the storefront needs
 * observers at all: prices, stock and inventory all live on `varieties`, and
 * both apps write them — the panel through the product form and `OrderObserver`
 * (status changes consume or release stock), the storefront through
 * `DecrementInventoryAndMarkPaid` on a successful payment.
 *
 * The cached product page therefore carries a stock count that a purchase can
 * make wrong, which is exactly what this class prevents. Nothing here is load
 * bearing for *correctness* of a sale: `CartController`, `ValidateCartStock`
 * and the row-locked decrement each re-read live inventory before money moves
 * (see `ORDER.md`). What goes stale without this is what the customer is shown.
 */
class VarietyObserver
{
    use ForgetsProductCache;

    /**
     * A product card's price is the cheapest published variety
     * (`CalculatePricing::forVarieties`) and its image can fall back to a
     * variety photo, so these columns move the listings too.
     *
     * `inventory` and `has_stock` are deliberately absent: they reach the
     * product page and no card, so selling a unit refreshes that one page and
     * leaves every category listing warm.
     */
    private const array CARD_COLUMNS = ['price', 'sale_price', 'status', 'product_id'];

    public function saved(Variety $variety): void
    {
        $this->invalidate($variety);
    }

    /**
     * `decrement('inventory')` fires only `updated`, never `saved` — hooking
     * both is what makes a paid order reach this class. See `ProductObserver`.
     */
    public function updated(Variety $variety): void
    {
        $this->invalidate($variety);
    }

    public function deleted(Variety $variety): void
    {
        $this->forgetProducts(
            [$variety->product_id, $this->originalProductId($variety)],
            affectsCards: true,
        );
    }

    private function invalidate(Variety $variety): void
    {
        $changed = array_keys($variety->getChanges());

        $this->forgetProducts(
            [$variety->product_id, $this->originalProductId($variety)],
            affectsCards: $changed === [] || array_intersect($changed, self::CARD_COLUMNS) !== [],
        );
    }

    /**
     * A variety moved to another product has to clear both products' pages.
     */
    private function originalProductId(Variety $variety): ?int
    {
        $productId = $variety->getOriginal('product_id');

        return is_numeric($productId) ? (int) $productId : null;
    }
}
