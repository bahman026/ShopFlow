<?php

declare(strict_types=1);

return [
    'label' => 'Product',
    'plural_label' => 'Products',
    'navigation_group' => 'Catalog',
    'subheading' => 'Products are the items sold on ShopFlow. Each product belongs to a category and brand, has a base price, and can have multiple varieties (e.g. different colors or sizes) each with their own price and inventory. Set an attribute group to define which attribute type differentiates the varieties. Product-level attributes describe shared specifications shown on the product page.',

    // Core fields
    'heading' => 'Name',
    'slug' => 'Slug',
    'slug_hint' => 'Auto-generated from the heading on create. Cannot be changed after creation.',
    'price' => 'Base Price',
    'price_hint' => 'Base price shown when no variety is selected.',
    'content' => 'Content',
    'title' => 'Meta Title',
    'title_hint' => 'SEO <title> tag. If empty, the heading is used.',
    'description' => 'Meta Description',
    'description_hint' => 'SEO meta description shown in search results.',
    'no_index' => 'No Index',
    'no_index_hint' => 'When on, adds a noindex meta tag so search engines skip this page.',
    'canonical' => 'Canonical URL',
    'canonical_hint' => 'Canonical URL to prevent duplicate content. Leave empty unless this product mirrors another page.',

    // Images repeater
    'images' => 'Images',
    'path' => 'Image File',
    'is_featured' => 'Featured Image',
    'alt_text' => 'Alt Text',

    // Relations
    'category_id' => 'Category',
    'category_id_hint' => 'The category this product belongs to. Required attribute groups for this category will be enforced on save.',
    'attribute_group_id' => 'Attribute Group',
    'attribute_group_id_hint' => 'Defines which attribute group differentiates the varieties of this product (e.g. "Color"). Filtered by the selected category. Changing this reloads attribute options in the variety rows below.',
    'brand_id' => 'Brand',

    // Quantity rules
    'minimum' => 'Minimum Quantity',
    'minimum_hint' => 'Minimum quantity a customer can add to their cart.',
    'maximum' => 'Maximum Quantity',
    'maximum_hint' => 'Maximum quantity per order. Leave empty for no limit.',
    'step' => 'Quantity Step',
    'step_hint' => 'Quantity increment step (e.g. 2 means customers can add 2, 4, 6...).',
    'profit_percent' => 'Profit %',
    'profit_percent_hint' => "ShopFlow's commission percentage from each sale of this product.",

    // Product-level attributes repeater
    'attributes' => 'Product Attributes',
    'attributes_hint' => 'Product-level attributes that describe this product (e.g. material, dimensions). These are not varieties — they are shared across all varieties. Mark an attribute as Highlight to feature it prominently on the product page.',
    'attribute_id' => 'Attribute',
    'is_highlight' => 'Highlight',

    // Misc
    'has_stock' => 'Has Stock',
    'variety_counts' => 'Variety Count',
    'variety_counts_helper' => 'Calculated automatically from the varieties below.',
    'weight' => 'Weight',
    'length' => 'Length',
    'width' => 'Width',
    'height' => 'Height',
    'status' => 'Status',
    'status_deleted' => 'Deleted',
    'status_published' => 'Published',
    'status_draft' => 'Draft',
    'seen' => 'Views',

    // Varieties repeater
    'varieties' => 'Varieties',
    'varieties_hint' => 'Each row is a variety of this product. The attribute options depend on the Attribute Group selected above — set that first.',
    'variety_attribute_id' => 'Attribute',
    'variety_attribute_helper' => 'Selecting an attribute auto-fills the value and color.',
    'variety_color' => 'Color',
    'variety_color_hint' => 'Hex color for this variety (e.g. #ff8516). Auto-filled from the selected attribute — override here if needed.',
    'variety_additional_attributes' => 'Additional Attributes',
    'variety_additional_attributes_hint' => 'Additional attributes from other groups, e.g. Color when the primary group is Size.',
    'variety_price' => 'Price',
    'variety_sale_price' => 'Sale Price',
    'variety_inventory' => 'Inventory',
    'variety_has_stock' => 'Has Stock',
    'variety_status' => 'Status',
    'variety_image' => 'Variety Image',

    // Table columns
    'featured' => 'Featured',
    'attribute_group' => 'Attribute Group',
    'category' => 'Category',
    'brand' => 'Brand',
    'created_at' => 'Created At',
    'updated_at' => 'Updated At',
];
