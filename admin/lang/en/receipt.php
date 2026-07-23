<?php

declare(strict_types=1);

return [
    'label' => 'Receipt',
    'plural_label' => 'Receipts',
    'navigation_group' => 'Commerce',
    'subheading' => 'Manual/offline payments only (card-to-card, Paya transfer, prepayment) recorded against users and (optionally) orders. Online gateway payments (Zarinpal, Mellat, Parsian) never appear here — check the Transactions table for those instead.',

    'user_id' => 'User',
    'card_id' => 'Card ID',
    'amount' => 'Amount',
    'order_id' => 'Order',
    'tracking_code' => 'Tracking Code',
    'paid_at' => 'Paid At',
    'destination_name' => 'Account Receiver',
    'destination_bank' => 'Bank',
    'end_of_card_number' => 'Card Last 4 Digits',
    'description' => 'Description',
    'is_paya' => 'Paya Transfer',
    'type' => 'Type',
    'image' => 'Receipt Image',
    'path' => 'Image File',
    'alt_text' => 'Alt Text',
    'created_at' => 'Created At',
    'updated_at' => 'Updated At',

    'type_receipt' => 'Receipt',
    'type_prepayment' => 'Prepayment',
    'type_shipping_request' => 'Shipping Request',
];
