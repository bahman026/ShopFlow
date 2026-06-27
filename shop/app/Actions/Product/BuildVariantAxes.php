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

                $axes[$groupId]['options'][$attribute->value] ??= [
                    'value' => $attribute->value,
                    'color' => $attribute->color,
                ];
            }
        }

        $primaryGroupId = $this->primaryGroupId($primaryVotes);

        return array_values(array_map(
            fn (array $axis): array => [
                'id' => $axis['id'],
                'name' => $axis['name'],
                'primary' => $axis['id'] === $primaryGroupId,
                'options' => array_values($axis['options']),
            ],
            $axes,
        ));
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
