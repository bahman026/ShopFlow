<?php

declare(strict_types=1);

return [
    'label' => 'Banner',
    'plural_label' => 'Banners',
    'navigation_group' => 'Content',

    'position' => 'Position',
    'position_hint' => 'Where this banner appears on the storefront. Only the fixed placements the frontend knows about are offered.',
    'position_home_top' => 'Home — top',
    'position_home_top_description' => 'Full-width strip at the very top of the home page, above the main slider. Only the first banner here is shown.',
    'position_home_middle' => 'Home — middle grid',
    'position_home_middle_description' => 'Promo grid in the middle of the home page, below the categories. Up to three banners sit side by side.',
    'position_category_side' => 'Category page — side',
    'position_category_side_description' => 'Stacked in the sidebar of every category page, under the filters. Hidden on mobile, where the sidebar collapses.',
    'heading' => 'Heading',
    'url' => 'URL',
    'url_hint' => 'Where the banner links to. Use an absolute URL (https://…) or an internal path such as /tags/gaming-gear or /categories/mobile.',
    'url_invalid' => 'Enter an absolute URL (https://…) or an internal path starting with /.',
    'sort' => 'Sort',
    'status' => 'Status',
    'images' => 'Images',
    'path' => 'Image File',
    'path_hint' => 'Cropped to :ratio for this position. Upload at least :size pixels so it stays sharp.',
    'path_hint_no_position' => 'Choose a position first — it decides the crop ratio for this image.',
    'is_featured' => 'Featured Image',
    'alt_text' => 'Alt Text',
    'featured' => 'Featured',
    'created_at' => 'Created At',
    'updated_at' => 'Updated At',

    'status_deleted' => 'Deleted',
    'status_published' => 'Published',
    'status_draft' => 'Draft',
];
