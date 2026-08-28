<?php

declare(strict_types=1);

use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\Variety;

/**
 * `varieties.attribute_value` and `varieties.color` are copied from the linked
 * attribute by `Variety::booted()`, but only when `attribute_id` itself changes.
 * Renaming an attribute afterwards therefore used to leave every existing
 * variety holding the old text: the product page's variant axis (read live off
 * the relation) showed the new value while the selected variety's label showed
 * the old one — a page disagreeing with itself, and one no cache invalidation
 * could fix because the stored data was wrong.
 *
 * `AttributeObserver` now pushes the change down, but only onto rows that were
 * still in sync. The override cases below are the point of the whole design:
 * `color` is a `ColorPicker` on both the variety form and the product form's
 * variety repeater, so a per-variety swatch deliberately differing from its
 * attribute is something staff really do, and it must survive a rename.
 */
beforeEach(function () {
    login();
});

/**
 * An attribute plus a variety already in sync with it, exactly as
 * `Variety::booted()` leaves things after the attribute is first linked.
 *
 * @return array{0: Attribute, 1: Variety}
 */
function syncedVariety(string $value = 'قرمز', ?string $color = '#ff0000'): array
{
    $attribute = Attribute::factory()->create(['value' => $value, 'color' => $color]);

    $variety = Variety::factory()->create([
        'attribute_id' => $attribute->id,
        'attribute_value' => $value,
        'color' => $color,
    ]);

    return [$attribute, $variety];
}

it('pushes a renamed attribute value onto the varieties that copied it', function () {
    [$attribute, $variety] = syncedVariety();

    $attribute->update(['value' => 'زرشکی']);

    expect($variety->refresh()->attribute_value)->toBe('زرشکی');
});

it('pushes a changed attribute colour onto the varieties that copied it', function () {
    [$attribute, $variety] = syncedVariety();

    $attribute->update(['color' => '#00ff00']);

    expect($variety->refresh()->color)->toBe('#00ff00');
});

it('leaves a manually overridden variety colour alone on a rename', function () {
    // The case the guard exists for: staff picked this swatch by hand in the
    // panel's ColorPicker, so it is not the attribute's colour to reclaim.
    [$attribute, $variety] = syncedVariety();
    $variety->update(['color' => '#123456']);

    $attribute->update(['color' => '#00ff00']);

    expect($variety->refresh()->color)->toBe('#123456');
});

it('leaves a manually overridden variety label alone on a rename', function () {
    [$attribute, $variety] = syncedVariety();
    $variety->update(['attribute_value' => 'قرمز آتشین']);

    $attribute->update(['value' => 'زرشکی']);

    expect($variety->refresh()->attribute_value)->toBe('قرمز آتشین');
});

it('resyncs the label and the colour independently', function () {
    // A variety can be in sync on one column and overridden on the other.
    [$attribute, $variety] = syncedVariety();
    $variety->update(['color' => '#123456']);

    $attribute->update(['value' => 'زرشکی', 'color' => '#00ff00']);

    expect($variety->refresh()->attribute_value)->toBe('زرشکی')
        ->and($variety->color)->toBe('#123456');
});

it('only touches varieties linked to the renamed attribute', function () {
    [$attribute, $variety] = syncedVariety();
    [, $other] = syncedVariety('آبی', '#0000ff');

    $attribute->update(['value' => 'زرشکی']);

    expect($variety->refresh()->attribute_value)->toBe('زرشکی')
        ->and($other->refresh()->attribute_value)->toBe('آبی');
});

it('fills a null variety colour when the attribute gains one', function () {
    // The previous value being null needs IS NULL, not = NULL, or nothing matches.
    [$attribute, $variety] = syncedVariety('قرمز', null);

    $attribute->update(['color' => '#ff0000']);

    expect($variety->refresh()->color)->toBe('#ff0000');
});

it('keeps the copied label when the attribute is deleted', function () {
    // varieties.attribute_id is nullOnDelete, so the link goes. Keeping the
    // copied text means the variety still renders a readable label instead of
    // going blank.
    [$attribute, $variety] = syncedVariety();

    $attribute->delete();

    expect($variety->refresh()->attribute_value)->toBe('قرمز')
        ->and($variety->attribute_id)->toBeNull();
});

it('leaves varieties alone when only the attribute group changes', function () {
    [$attribute, $variety] = syncedVariety();
    $group = AttributeGroup::factory()->create();

    $attribute->update(['attribute_group_id' => $group->id]);

    expect($variety->refresh()->attribute_value)->toBe('قرمز')
        ->and($variety->color)->toBe('#ff0000');
});
