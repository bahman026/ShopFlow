<?php

declare(strict_types=1);

namespace App\Actions\Brand;

use App\Models\Brand;

class BuildBrandBreadcrumbs
{
    /**
     * Breadcrumb trail: home, then the brand as the active (link-less) crumb.
     *
     * @return array<int, array{heading: string, url: string|null}>
     */
    public function __invoke(Brand $brand): array
    {
        return [
            ['heading' => 'خانه', 'url' => '/'],
            ['heading' => $brand->heading, 'url' => null],
        ];
    }
}
