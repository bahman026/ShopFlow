<?php

declare(strict_types=1);

return [
    'label' => 'Shipping Method',
    'plural_label' => 'Shipping Methods',
    'navigation_group' => 'Logistics',
    'subheading' => 'Service tiers offered by each shipping carrier (e.g. Standard Post, Overnight Express). Each method can then be configured per city.',

    'shipping_line_id' => 'Carrier',
    'shipping_line_id_hint' => 'The carrier this method belongs to (e.g. Post Office, Tap30).',
    'name' => 'Method Name',
    'name_hint' => 'The service name shown to admins (e.g. "Standard Post", "Overnight Express").',
    'type' => 'Service Type',
    'type_hint' => 'Service category, e.g. Express, Economy, Special.',
    'min_count' => 'Minimum Item Count',
    'min_count_hint' => 'Minimum item quantity required to use this method. Leave empty for no minimum.',
    'min_amount' => 'Minimum Order Amount',
    'min_amount_hint' => 'Minimum order amount required to use this method. Leave empty for no minimum.',
    'for' => 'Audience',
    'for_hint' => 'Who can use this method — Customer, Partner, or Employee.',
    'disable_from' => 'Disabled From',
    'disable_from_hint' => 'Start of the period when this method is unavailable (e.g. holiday blackout).',
    'disable_to' => 'Disabled To',
    'disable_to_hint' => 'End of the unavailability period.',
    'status' => 'Active',
    'status_hint' => 'When off, this method is hidden from all users.',

    'for_customer' => 'Customer',
    'for_partner' => 'Partner',
    'for_employee' => 'Employee',

    'carrier' => 'Carrier',
    'no_minimum' => 'No minimum',
    'created_at' => 'Created At',
];
