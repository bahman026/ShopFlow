<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * One case per image slot in the panel, carrying the shape the storefront
 * actually renders that slot at.
 *
 * Banners and sliders are deliberately absent: their shape depends on the
 * position chosen on the record, so it lives on BannerPositionEnum and
 * SliderPositionEnum instead. Everything else has one fixed shape, and it
 * lives here so the panel and the storefront cannot drift apart silently.
 *
 * A null aspectRatio() means "must not be cropped", not "not decided" — see
 * the note on each case below.
 */
enum ImageAspectEnum: string
{
    /** Product gallery + card. Rendered in an aspect-square frame. */
    case PRODUCT = 'product';

    /** A variety's own photo. Shares the product gallery, so same shape. */
    case VARIETY = 'variety';

    /** Home category strip. A 64-80px circle using object-cover. */
    case CATEGORY = 'category';

    /** CMS page hero. Full article width with no height frame at all. */
    case PAGE = 'page';

    /** Tag landing page hero. Not rendered yet; shaped like PAGE for when it is. */
    case TAG = 'tag';

    /** Brand logo. object-contain at a fixed height, so never cropped. */
    case BRAND = 'brand';

    /** Payment gateway logo. Panel-only, same reasoning as BRAND. */
    case GATEWAY = 'gateway';

    /** Menu item icon. Not rendered by the storefront at all. */
    case MENU_ITEM = 'menu-item';

    /** Customer's proof of payment. Evidence — cropping it destroys data. */
    case RECEIPT = 'receipt';

    /**
     * The ratio this slot renders at, in Filament's crop format, or null when
     * the upload must be left alone.
     *
     * Null is a decision, not a gap:
     * - BRAND / GATEWAY are logos drawn with object-contain, so a wide wordmark
     *   is already safe. A forced square would cut it or make staff pad it.
     * - MENU_ITEM has no render site in the storefront to take a shape from.
     * - RECEIPT is a bank receipt. A crop can cut off the reference number,
     *   amount or date, which is the whole point of the file.
     */
    public function aspectRatio(): ?string
    {
        return match ($this) {
            // Square: the gallery, the card and the category circle are all 1:1.
            self::PRODUCT, self::VARIETY, self::CATEGORY => '1:1',
            // Wide: a page hero has no height frame, so an uncropped portrait
            // upload would push the whole article down the screen.
            self::PAGE, self::TAG => '16:9',
            self::BRAND, self::GATEWAY, self::MENU_ITEM, self::RECEIPT => null,
        };
    }

    /**
     * Recommended source dimensions in pixels — roughly twice the rendered
     * size, so the image stays sharp on a retina screen without being
     * wasteful. For the uncropped slots this is a guide, not a crop target.
     */
    public function recommendedSize(): string
    {
        return match ($this) {
            self::PRODUCT, self::VARIETY => '1000 × 1000',
            // Renders at 80px, but it is also the category page's share image.
            self::CATEGORY => '600 × 600',
            self::PAGE, self::TAG => '1600 × 900',
            // Drawn 48px tall; wordmarks are usually about twice as wide as tall.
            self::BRAND => '400 × 200',
            self::GATEWAY, self::MENU_ITEM => '200 × 200',
            // A phone photo of a receipt, portrait.
            self::RECEIPT => '1200 × 1600',
        };
    }

    /**
     * Upload ceiling in kilobytes. Generous enough for a real photo, low
     * enough that nobody drops a 20MB camera original into the panel.
     */
    public function maxSizeKb(): int
    {
        return match ($this) {
            self::PAGE, self::TAG, self::RECEIPT => 4096,
            default => 2048,
        };
    }

    /**
     * The line of help printed under the upload field, telling staff what
     * shape to bring and whether it will be cropped.
     */
    public function hint(): string
    {
        $ratio = $this->aspectRatio();

        if ($ratio === null) {
            return trans('system.image_hint_free', [
                'size' => $this->recommendedSize(),
            ]);
        }

        return trans('system.image_hint', [
            'ratio' => $ratio,
            'size' => $this->recommendedSize(),
        ]);
    }
}
