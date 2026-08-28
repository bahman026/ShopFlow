<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\AttributeGroup;
use App\Observers\Concerns\FlushesCatalogCache;

/**
 * Flushes the catalog cache when an attribute group changes.
 *
 * A cached product page renders the group's `name` twice, both live: as the
 * heading of each spec row (`BuildProductDetail::groupedSpecs()` groups by it)
 * and as the label of each variant axis (`BuildVariantAxes`). Renaming "سایز"
 * therefore changes every product that has any attribute in that group, which in
 * practice is most of a catalog — the reason this flushes rather than forgetting.
 *
 * Deleting a group cascades to its attributes (`attributes.attribute_group_id`
 * is `cascadeOnDelete`), and from there to `product_attribute` and
 * `attribute_variety`, all in the database with no Eloquent events — so a delete
 * can empty a product's whole spec table without any product being saved.
 */
class AttributeGroupObserver
{
    use FlushesCatalogCache;

    /**
     * `name` is what customers actually read as the spec/axis heading. `order`
     * decides the sequence facet groups appear in on a category page.
     *
     * `label` is deliberately absent: it is documented as an admin-panel-only
     * field that is never shown to a customer (`GetCategoryFilters` displays
     * `name`), so editing it must not cool the catalog.
     */
    private const array RENDERED_COLUMNS = ['name', 'order'];

    public function updated(AttributeGroup $attributeGroup): void
    {
        $this->flushWhenRenderedColumnsChanged($attributeGroup, self::RENDERED_COLUMNS);
    }

    public function deleted(AttributeGroup $attributeGroup): void
    {
        $this->flushForDelete();
    }
}
