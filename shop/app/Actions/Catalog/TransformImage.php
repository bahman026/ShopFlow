<?php

declare(strict_types=1);

namespace App\Actions\Catalog;

use App\DTOs\ImageDTO;
use App\Models\Image;

class TransformImage
{
    /**
     * Shape an image model into an ImageDTO (url + alt), or null when absent.
     */
    public function __invoke(?Image $image): ?ImageDTO
    {
        if ($image === null) {
            return null;
        }

        return new ImageDTO(
            url: $image->url,
            alt: (string) ($image->alt_text ?? ''),
        );
    }
}
