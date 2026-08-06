<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\HomeSectionTypeEnum;
use App\Models\HomeSection;
use Illuminate\Database\Seeder;

class HomeSectionSeeder extends Seeder
{
    /**
     * The default home-page layout (hero → tags → categories → banners →
     * two product rows → brands). Reference data, so it lives in
     * DatabaseSeeder and re-seeds idempotently by order.
     */
    public function run(): void
    {
        $sections = [
            ['type' => HomeSectionTypeEnum::SLIDER, 'title' => null, 'config' => ['position' => 'home-main'], 'order' => 1],
            ['type' => HomeSectionTypeEnum::TAGS, 'title' => null, 'config' => null, 'order' => 2],
            ['type' => HomeSectionTypeEnum::CATEGORIES, 'title' => null, 'config' => null, 'order' => 3],
            ['type' => HomeSectionTypeEnum::BANNERS, 'title' => null, 'config' => ['position' => 'home-middle'], 'order' => 4],
            ['type' => HomeSectionTypeEnum::PRODUCTS, 'title' => 'جدیدترین محصولات', 'config' => ['sort' => 'newest'], 'order' => 5],
            ['type' => HomeSectionTypeEnum::PRODUCTS, 'title' => 'پربازدیدترین محصولات', 'config' => ['sort' => 'popular'], 'order' => 6],
            ['type' => HomeSectionTypeEnum::BRANDS, 'title' => null, 'config' => null, 'order' => 7],
        ];

        foreach ($sections as $section) {
            HomeSection::updateOrCreate(
                ['order' => $section['order']],
                [...$section, 'status' => true],
            );
        }
    }
}
