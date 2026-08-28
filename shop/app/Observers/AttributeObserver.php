<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Attribute;
use App\Models\Variety;
use App\Observers\Concerns\FlushesCatalogCache;

/**
 * Keeps varieties and the catalog cache in step when an attribute changes.
 *
 * A cached product page reads attributes live in three separate places, which is
 * why the cache side of this cannot be narrowed to a single product:
 *
 * - the spec table (`BuildProductDetail::groupedSpecs()`, via `product_attribute`),
 * - the primary variant axis and its options (`BuildVariantAxes`, via `varieties.attribute_id`),
 * - the secondary axes (via the `attribute_variety` pivot).
 *
 * ## The denormalised half, and why flushing alone was not enough
 *
 * A variety's own label and colour are *copied onto the row*:
 * `Variety::booted()` fills `attribute_value` and `color` from the attribute
 * only when `attribute_id` itself changes, and `BuildProductDetail::variety()`
 * prefers those columns over the live relation. So renaming an attribute used to
 * update the axis buttons (read live) while the selected variety's label kept the
 * old text — a page disagreeing with itself, and one that no amount of cache
 * invalidation could fix, because the *data* was stale.
 *
 * `resyncDenormalisedVarieties()` closes that, and it runs **before** the flush
 * so the cache rebuilds from corrected rows rather than racing them.
 */
class AttributeObserver
{
    use FlushesCatalogCache;

    /**
     * The attribute column each variety column is copied from.
     */
    private const array DENORMALISED_COLUMNS = [
        'value' => 'attribute_value',
        'color' => 'color',
    ];

    /**
     * `value` and `color` are rendered directly as spec values, axis option
     * labels and colour swatches. `attribute_group_id` decides which spec group
     * and which axis the value appears under, so moving an attribute between
     * groups reshapes the page just as much as renaming it.
     */
    private const array RENDERED_COLUMNS = ['value', 'color', 'attribute_group_id'];

    public function updated(Attribute $attribute): void
    {
        $this->resyncDenormalisedVarieties($attribute);

        $this->flushWhenRenderedColumnsChanged($attribute, self::RENDERED_COLUMNS);
    }

    /**
     * Deliberately no resync on delete. `varieties.attribute_id` is
     * `nullOnDelete`, so the link goes but the copied `attribute_value` stays —
     * which is what we want: the variety keeps a readable label ("قرمز") instead
     * of rendering blank once the attribute it named is gone.
     */
    public function deleted(Attribute $attribute): void
    {
        $this->flushForDelete();
    }

    /**
     * Push a renamed value/colour onto the varieties that copied it.
     *
     * Only rows still holding the **pre-change** value are touched. Those are the
     * ones `Variety::booted()` populated, so updating them restores the intent.
     * A row holding anything else was set deliberately and is left alone — that
     * matters most for `color`, which the panel exposes as a `ColorPicker` on
     * both the variety form and the product form's variety repeater, so a
     * per-variety swatch that differs from its attribute is a real thing staff
     * do. (`attribute_value` has no form field at all, but seeders write it, so
     * it gets the same treatment.)
     *
     * The mass `update()` fires no model events on purpose: `VarietyObserver`
     * would otherwise run once per row, and the `flushAll()` that follows
     * already covers every page those rows appear on.
     */
    private function resyncDenormalisedVarieties(Attribute $attribute): void
    {
        $changes = $attribute->getChanges();

        foreach (self::DENORMALISED_COLUMNS as $source => $target) {
            if (! array_key_exists($source, $changes)) {
                continue;
            }

            $query = Variety::query()->where('attribute_id', $attribute->getKey());

            // `attributes.color` is nullable, so the previous value can be null
            // — which needs `IS NULL`, not `= NULL`.
            $previous = $attribute->getOriginal($source);

            $previous === null
                ? $query->whereNull($target)
                : $query->where($target, $previous);

            $query->update([$target => $changes[$source]]);
        }
    }
}
