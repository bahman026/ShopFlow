<?php

declare(strict_types=1);

return [
    'label' => 'Tag',
    'plural_label' => 'Tags',
    'navigation_group' => 'Content',
    'subheading' => 'SEO landing pages for a category + attribute filter (e.g. "Gaming gear", "Red men\'s shoes"). Each tag has its own URL and lists the matching products; it is not a free-form product label.',

    'section_main' => 'Tag',
    'section_home' => 'Home Page',
    'section_seo' => 'SEO',

    'name' => 'Name',
    'slug' => 'Slug',
    'slug_hint' => 'The stable, human-readable URL segment: /tags/{slug}. Do not change it once published.',
    'category_id' => 'Category',
    'category_id_hint' => 'Optional. The category the tag scopes to (its sub-categories are included). Leave empty for an attribute-only tag. At least one of category or attributes is required.',
    'attributes' => 'Attributes',
    'attributes_hint' => 'Optional. One or more attributes the tag filters by (OR within a group, AND across groups). Leave empty for a category-only tag. At least one of category or attributes is required.',
    'content' => 'Content',
    'title' => 'SEO Title',
    'description' => 'SEO Description',
    'canonical' => 'Canonical URL',
    'no_index' => 'No Index',
    'show_on_home' => 'Show on Home Page',
    'show_on_home_hint' => 'When on, this tag appears in the storefront home page featured-tags strip (its image + name link to the tag page).',
    'home_order' => 'Home Order',
    'image' => 'Image',
    'path' => 'Image File',
    'created_at' => 'Created At',
];
