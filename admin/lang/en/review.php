<?php

declare(strict_types=1);

return [
    'label' => 'Review',
    'plural_label' => 'Reviews',
    'navigation_group' => 'Content',
    'subheading' => 'Moderate user reviews for products. Approve, reject, or reply to reviews from here.',

    'heading' => 'Title',
    'heading_hint' => 'The review title written by the user (e.g. "Great product!").',
    'content' => 'Review Text',
    'content_hint' => 'The full review text.',
    'product_id' => 'Product',
    'product_id_hint' => 'The product this review is about.',
    'variety_id' => 'Variety (optional)',
    'variety_id_hint' => 'The specific variety (e.g. size/color) the user purchased, if known.',
    'user_id' => 'User',
    'user_id_hint' => 'The user who wrote this review. Leave empty for anonymous/admin-entered reviews.',
    'parent_id' => 'Reply to',
    'parent_id_hint' => 'Set this to make the review a reply to another review.',
    'status' => 'Status',
    'status_hint' => 'Pending reviews are hidden from the storefront until approved.',
    'product' => 'Product',
    'user' => 'User',
    'anonymous' => 'Anonymous',
    'created_at' => 'Created At',

    'status_deleted' => 'Deleted',
    'status_pending' => 'Pending',
    'status_approved' => 'Approved',
    'status_rejected' => 'Rejected',
];
