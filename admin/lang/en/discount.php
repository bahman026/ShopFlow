<?php

declare(strict_types=1);

return [
    'label' => 'Discount',
    'plural_label' => 'Discounts',
    'navigation_group' => 'Promotions',
    'subheading' => 'Discounts are automatic price rules applied to a specific variety when their conditions are met — no code needed. Each discount targets one variety and can be limited by quantity, time window, audience, and usage cap. When an order qualifies, the discount amount is stored on the order for historical reference.',

    'variety_id' => 'Variety',
    'variety_id_hint' => 'The specific variety this discount applies to. Label shows: Product — Variety.',
    'quantity' => 'Minimum Quantity',
    'quantity_hint' => 'Minimum quantity the customer must purchase for this discount to apply.',
    'priority' => 'Priority',
    'priority_hint' => 'When multiple discounts qualify, the one with the highest priority wins.',
    'is_percent' => 'Percentage Discount',
    'is_percent_hint' => 'When on, the amount is treated as a percentage (e.g. 20 = 20% off). When off, it is a fixed amount in Tomans.',
    'amount' => 'Amount',
    'amount_hint' => 'The discount value. Interpreted as percent or fixed amount based on the toggle above.',
    'started_at' => 'Starts At',
    'started_at_hint' => 'When the discount becomes active. Leave empty to start immediately.',
    'ended_at' => 'Ends At',
    'ended_at_hint' => 'When the discount expires. Leave empty for no expiry.',
    'sold' => 'Times Used',
    'sold_hint' => 'Number of times this discount has already been used. Usually managed automatically.',
    'max_sell' => 'Total Usage Cap',
    'max_sell_hint' => 'Total usage cap across all customers. Leave empty for unlimited.',
    'max_sell_by_user' => 'Per-User Cap',
    'max_sell_by_user_hint' => 'Maximum times a single user can use this discount. Leave empty for unlimited.',
    'is_for' => 'Audience',
    'is_for_hint' => 'Restricts who can benefit from this discount: everyone, registered users only, or partners only.',

    'for_everyone' => 'Everyone',
    'for_users' => 'Registered Users',
    'for_partners' => 'Partners',

    'variety_value' => 'Variety',
    'created_at' => 'Created At',
    'updated_at' => 'Updated At',
];
