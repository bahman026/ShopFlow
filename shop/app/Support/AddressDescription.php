<?php

declare(strict_types=1);

namespace App\Support;

/**
 * The shared `addresses` table has no plate/unit columns, only a free-text
 * `description`. We round-trip the storefront's plate/unit/note through it as a
 * small JSON payload so the edit form can prefill each field separately.
 * Plain-text descriptions written elsewhere (e.g. the admin panel) decode as a
 * note so nothing is lost.
 */
class AddressDescription
{
    public static function encode(?string $plate, ?string $unit, ?string $note): ?string
    {
        $payload = array_filter(
            ['plate' => $plate, 'unit' => $unit, 'note' => $note],
            static fn (?string $value): bool => $value !== null && $value !== '',
        );

        if ($payload === []) {
            return null;
        }

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);

        return $json === false ? null : $json;
    }

    /**
     * @return array{plate: string|null, unit: string|null, note: string|null}
     */
    public static function decode(?string $description): array
    {
        if ($description === null || $description === '') {
            return ['plate' => null, 'unit' => null, 'note' => null];
        }

        $data = json_decode($description, true);

        if (! is_array($data)) {
            return ['plate' => null, 'unit' => null, 'note' => $description];
        }

        return [
            'plate' => self::stringOrNull($data['plate'] ?? null),
            'unit' => self::stringOrNull($data['unit'] ?? null),
            'note' => self::stringOrNull($data['note'] ?? null),
        ];
    }

    private static function stringOrNull(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }
}
