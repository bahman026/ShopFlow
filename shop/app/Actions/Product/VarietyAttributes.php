<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Attribute;
use App\Models\Variety;

class VarietyAttributes
{
    /**
     * The grouped attributes that define a variety: its primary attribute
     * (attribute_id) first, then any additional pivoted attributes.
     *
     * @return array<int, Attribute>
     */
    public function __invoke(Variety $variety): array
    {
        $attributes = [];

        if ($variety->attribute !== null) {
            $attributes[] = $variety->attribute;
        }

        foreach ($variety->attributes as $attribute) {
            $attributes[] = $attribute;
        }

        return $attributes;
    }
}
