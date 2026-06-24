<?php

declare(strict_types=1);

namespace App\Traits;

trait HasOptions
{
    /**
     * @return array<int|string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $enum) => [
                $enum->value => $enum->label(),
            ])
            ->toArray();
    }
}
