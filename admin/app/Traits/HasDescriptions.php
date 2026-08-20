<?php

declare(strict_types=1);

namespace App\Traits;

/**
 * Companion to HasOptions for enums whose values need explaining, not just
 * labelling. Feeds Filament's Radio::descriptions(), which prints one line of
 * help under each option.
 *
 * The enum must implement description(): string.
 */
trait HasDescriptions
{
    /**
     * @return array<string, string>
     */
    public static function descriptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $enum) => [
                $enum->value => $enum->description(),
            ])
            ->toArray();
    }
}
