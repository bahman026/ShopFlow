<?php

declare(strict_types=1);

return [
    'label' => 'Variety',
    'plural_label' => 'Varieties',
    'navigation_group' => 'Catalog',
    'subheading' => "Varieties are the purchasable options of a product (e.g. a T-shirt in Red or Blue, each with its own price and inventory). Each variety belongs to one product and is linked to one attribute from the product's attribute group. Selecting an attribute auto-fills the variety's label and color.",

    'product_id' => 'Product',
    'product_id_hint' => 'The product this variety belongs to. Changing it reloads the available attributes below.',
    'attribute_id' => 'Attribute',
    'attribute_id_hint' => "The attribute that defines this variety (e.g. \"Red\" from the \"Color\" group). Auto-fills value and color on save. Options are filtered by the product's attribute group.",
    'attribute_id_helper' => 'Selecting an attribute auto-fills the value and color.',
    'color' => 'Color',
    'color_hint' => 'Hex color for this variety (e.g. #ff8516). Auto-filled from the selected attribute — override here if needed.',
    'price' => 'Price',
    'price_hint' => 'The selling price of this variety.',
    'sale_price' => 'Sale Price',
    'sale_price_hint' => 'Discounted price shown instead of the regular price when set. Leave empty for no sale price.',
    'inventory' => 'Inventory',
    'inventory_hint' => 'Number of units available in stock.',
    'has_stock' => 'Has Stock',
    'has_stock_hint' => 'When off, this variety is shown as out of stock regardless of inventory count.',
    'status' => 'Status',
    'additional_attributes' => 'Additional Attributes',
    'additional_attributes_hint' => 'Secondary attributes for this variety from other groups, e.g. Color when the primary group is Size.',

    'product' => 'Product',
    'attribute_value' => 'Value',
    'created_at' => 'Created At',
    'updated_at' => 'Updated At',
];
