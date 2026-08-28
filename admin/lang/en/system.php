<?php

declare(strict_types=1);

return [

    'health_label' => 'Server health',
    'health_subheading' => 'Slow requests and queries, exceptions, cache and queue activity, and this server\'s CPU, memory and disk. Recorded by both the panel and the storefront.',

    'logs_label' => 'Logs',
    'logs_subheading' => 'Log files for the panel and the storefront, in one folder each.',

    // Shared help under every upload field whose shape is fixed (ImageAspectEnum).
    'image_hint' => 'Cropped to :ratio. Upload at least :size pixels so it stays sharp.',
    'image_hint_free' => 'Not cropped — the whole image is kept. Recommended size :size pixels.',
];
