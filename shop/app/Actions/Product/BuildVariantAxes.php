<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Variety;
use Illuminate\Database\Eloquent\Collection;

class BuildVariantAxes
{
    public function __construct(private VarietyAttributes $varietyAttributes) {}

    /**
     * Build selectable axes (one per attribute group) from the product's
     * varieties, e.g. a "color" axis and a "size" axis. Each axis lists its
     * distinct option values, keeping a hex color when the attribute has one.
     *
     * @param  Collection<int, Variety>  $varieties
     * @return array<int, array{id: int, name: string, primary: bool, options: array<int, array{value: string, color: string|null}>}>
     */
    public function __invoke(Collection $varieties): array
    {
        $axes = [];

        // The primary axis is the group most varieties pin via their primary
        // attribute (attribute_id). Counting keeps it stable even if a single
        // variety has inconsistent data; ties fall back to first seen.
        $primaryVotes = [];

        foreach ($varieties as $variety) {
            if ($variety->attribute !== null) {
                $groupId = $variety->attribute->attribute_group_id;
                $primaryVotes[$groupId] = ($primaryVotes[$groupId] ?? 0) + 1;
            }

            foreach (($this->varietyAttributes)($variety) as $attribute) {
                $groupId = $attribute->attribute_group_id;

                $axes[$groupId] ??= [
                    'id' => $groupId,
                    'name' => (string) $attribute->attributeGroup->name,
                    'options' => [],
                ];

                // Keyed by attribute id (not value) so options are dedupe-safe
                // and can be sorted deterministically below.
                $axes[$groupId]['options'][$attribute->id] ??= [
                    'value' => $attribute->value,
                    'color' => $attribute->color,
                ];
            }
        }

        $primaryGroupId = $this->primaryGroupId($primaryVotes);

        $result = array_values(array_map(
            fn (array $axis): array => [
                'id' => $axis['id'],
                'name' => $axis['name'],
                'primary' => $axis['id'] === $primaryGroupId,
                'options' => $this->sortedOptions($axis['options']),
            ],
            $axes,
        ));

        return $this->primaryFirst($result, $primaryGroupId);
    }

    /**
     * Options in attribute-creation order (`attributes` has no explicit
     * `order` column, so id order is the best deterministic proxy available)
     * instead of whatever order varieties happened to be processed in.
     *
     * @param  array<int, array{value: string, color: string|null}>  $options  keyed by attribute id
     * @return array<int, array{value: string, color: string|null}>
     */
    private function sortedOptions(array $options): array
    {
        ksort($options);

        return array_values($options);
    }

    /**
     * The primary axis always renders first, regardless of which attribute
     * group was encountered first while iterating varieties — e.g. a variety
     * whose primary attribute was deleted (attribute_id set to null) still
     * carries secondary attributes, and must never push the primary axis
     * further down the list just because it was processed first.
     *
     * @param  array<int, array{id: int, name: string, primary: bool, options: array<int, array{value: string, color: string|null}>}>  $axes
     * @return array<int, array{id: int, name: string, primary: bool, options: array<int, array{value: string, color: string|null}>}>
     */
    private function primaryFirst(array $axes, ?int $primaryGroupId): array
    {
        if ($primaryGroupId === null) {
            return $axes;
        }

        $primary = array_values(array_filter($axes, fn (array $axis): bool => $axis['id'] === $primaryGroupId));
        $rest = array_values(array_filter($axes, fn (array $axis): bool => $axis['id'] !== $primaryGroupId));

        return [...$primary, ...$rest];
    }

    /**
     * The most-voted attribute group, or null when no variety has a primary.
     *
     * @param  array<int, int>  $primaryVotes
     */
    private function primaryGroupId(array $primaryVotes): ?int
    {
        if ($primaryVotes === []) {
            return null;
        }

        $primaryGroupId = array_key_first($primaryVotes);
        foreach ($primaryVotes as $groupId => $votes) {
            if ($votes > $primaryVotes[$primaryGroupId]) {
                $primaryGroupId = $groupId;
            }
        }

        return $primaryGroupId;
    }
}
