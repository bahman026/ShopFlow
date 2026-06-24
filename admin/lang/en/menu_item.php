<?php

declare(strict_types=1);

return [
    'label' => 'Menu Item',
    'plural_label' => 'Menu Items',
    'navigation_group' => 'Content',
    'subheading' => 'Menu items are the individual links inside a menu. Each item belongs to one menu and can optionally be nested under a top-level item as a child. Use the Order field to control their display sequence.',

    'menu_id' => 'Menu',
    'menu_id_hint' => 'The menu this item belongs to. Changing it reloads the available parent options below.',
    'parent_id' => 'Parent Item',
    'parent_id_hint' => 'Optional. Select a top-level item to nest this item under it. Only top-level items are shown to prevent deep nesting.',
    'name' => 'Name',
    'name_hint' => 'The link text shown to the visitor, e.g. "About Us".',
    'url' => 'URL',
    'url_hint' => 'The URL this item links to. Use a relative path (e.g. /about) for internal pages or a full URL for external links.',
    'label_field' => 'Label',
    'label_field_hint' => 'Optional secondary badge or tag shown beside the item name, e.g. "New" or "Sale".',
    'order' => 'Order',
    'order_hint' => 'Display order within the menu or under the parent item. Lower numbers appear first.',
    'image' => 'Image',
    'path' => 'Image File',
    'alt_text' => 'Alt Text',
    'menu' => 'Menu',
    'parent' => 'Parent',
    'created_at' => 'Created At',
    'updated_at' => 'Updated At',
];
