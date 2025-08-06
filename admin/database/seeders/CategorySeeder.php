<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CategoryStatusEnum;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr; // Import the Arr facade

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'id' => 1,
                'heading' => 'لوازم جانبی گوشی',
                'slug' => 'mobile-accessories',
                'content' => 'انواع لوازم جانبی گوشی موبایل.',
                'title' => 'لوازم جانبی گوشی',
                'description' => 'لوازم جانبی متنوع برای گوشی‌های موبایل.',
                'no_index' => false,
                'canonical' => null,
                'parent_id' => null,
                'status' => CategoryStatusEnum::ACTIVE,
                'children' => [
                    [
                        'id' => 2,
                        'heading' => 'قاب و کاور گوشی',
                        'slug' => 'phone-cases',
                        'content' => 'محافظ‌های متنوع برای گوشی‌های موبایل.',
                        'title' => 'قاب و کاور گوشی',
                        'description' => 'انواع قاب و کاور با طراحی‌های زیبا.',
                        'no_index' => false,
                        'canonical' => null,
                        'parent_id' => 1,
                        'status' => CategoryStatusEnum::ACTIVE,
                    ],
                    [
                        'id' => 3,
                        'heading' => 'محافظ صفحه نمایش',
                        'slug' => 'screen-protectors',
                        'content' => 'محافظ صفحه‌های مختلف برای گوشی.',
                        'title' => 'محافظ صفحه نمایش',
                        'description' => 'محافظت از صفحه نمایش گوشی شما.',
                        'no_index' => false,
                        'canonical' => null,
                        'parent_id' => 1,
                        'status' => CategoryStatusEnum::ACTIVE,
                    ],
                    [
                        'id' => 4,
                        'heading' => 'پاوربانک',
                        'slug' => 'power-banks',
                        'content' => 'انواع پاوربانک با ظرفیت‌های مختلف.',
                        'title' => 'پاوربانک',
                        'description' => 'پاوربانک‌های باکیفیت برای شارژ دستگاه‌ها.',
                        'no_index' => false,
                        'canonical' => null,
                        'parent_id' => 1,
                        'status' => CategoryStatusEnum::ACTIVE,
                    ],
                ],
            ],
            [
                'id' => 5,
                'heading' => 'لوازم خانگی',
                'slug' => 'home-appliances',
                'content' => 'محصولات برقی و لوازم خانگی با کیفیت.',
                'title' => 'لوازم خانگی',
                'description' => 'لوازم خانگی متنوع و مدرن.',
                'no_index' => false,
                'canonical' => null,
                'parent_id' => null,
                'status' => CategoryStatusEnum::ACTIVE,
                'children' => [
                    [
                        'id' => 6,
                        'heading' => 'یخچال و فریزر',
                        'slug' => 'refrigerators',
                        'content' => 'انواع یخچال و فریزر با تکنولوژی روز.',
                        'title' => 'یخچال و فریزر',
                        'description' => 'لوازم ضروری برای نگهداری مواد غذایی.',
                        'no_index' => false,
                        'canonical' => null,
                        'parent_id' => 5,
                        'status' => CategoryStatusEnum::ACTIVE,
                    ],
                ],
            ],
        ];

        foreach ($categories as $category) {
            // Remove the 'children' key before inserting
            $categoryData = Arr::except($category, 'children');

            Category::query()->updateOrCreate(
                ['id' => $category['id']],
                $categoryData
            );

            if (isset($category['children'])) {
                $this->createChildren($category['children'], $category['id']);
            }
        }
    }

    private function createChildren(array $children, int $parentId): void
    {
        foreach ($children as $child) {
            $child['parent_id'] = $parentId;

            // Remove the 'children' key before inserting
            $childData = Arr::except($child, 'children');

            Category::query()->updateOrCreate(
                ['id' => $child['id']],
                $childData
            );

            if (isset($child['children'])) {
                $this->createChildren($child['children'], $child['id']);
            }
        }
    }
}
