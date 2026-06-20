<?php

declare(strict_types=1);

return [
    'label' => 'Shipping City',
    'plural_label' => 'Shipping Cities',
    'navigation_group' => 'Logistics',
    'subheading' => 'Per-city (or per-province) shipping availability, cost overrides, and delivery schedules for each shipping method.',

    'shipping_method_id' => 'Shipping Method',
    'shipping_method_id_hint' => 'The shipping method this city config applies to.',
    'province_id' => 'Province',
    'province_id_hint' => 'Set a province for a province-wide rule. Leave empty if targeting a specific city.',
    'city_id' => 'City',
    'city_id_hint' => 'Set a city for city-specific rules. City rules take priority over province rules. At least one of city or province must be set.',
    'amount' => 'Shipping Cost',
    'amount_hint' => 'Shipping cost for this location. Leave empty for postpaid. Set 0 for free shipping.',
    'pay_on_delivery' => 'Pay on Delivery',
    'pay_on_delivery_hint' => 'When on, the customer pays at delivery — no online payment required.',
    'sending_days' => 'Sending Days',
    'sending_days_hint' => 'Days this method ships for this location (comma-separated day numbers). Leave empty for every day.',
    'delay' => 'Delivery Delay',
    'delay_hint' => 'Extra delivery delay in days for this location (e.g. 1 for next-day).',
    'delay_suffix' => 'days',
    'description' => 'Description',
    'description_hint' => 'Human-readable delivery details shown to the customer (e.g. "Delivered within 72 hours").',
    'disable_from' => 'Disabled From',
    'disable_from_hint' => 'Start of a period when this method is unavailable in this location.',
    'disable_to' => 'Disabled To',
    'disable_to_hint' => 'End of the unavailability period.',
    'status' => 'Active',
    'status_hint' => 'When off, this entry is ignored at checkout.',

    'method' => 'Method',
    'province' => 'Province',
    'city' => 'City',
    'postpaid' => 'Postpaid',
    'created_at' => 'Created At',
];
