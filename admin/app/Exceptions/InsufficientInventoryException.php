<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when an order is moved into a stock-consuming status but a line no
 * longer has enough inventory to cover it.
 *
 * The transition is refused rather than allowed to push `varieties.inventory`
 * negative (the column is unsigned, so the database would reject it anyway —
 * but with a 500 instead of something staff can act on).
 */
class InsufficientInventoryException extends RuntimeException
{
    public function __construct(
        public readonly string $varietyLabel,
        public readonly int $available,
        public readonly int $required,
    ) {
        parent::__construct(sprintf(
            'Variety %s has %d in stock but the order needs %d.',
            $varietyLabel,
            $available,
            $required,
        ));
    }

    public function forHumans(): string
    {
        return trans('order.inventory_insufficient', [
            'variety' => $this->varietyLabel,
            'available' => $this->available,
            'required' => $this->required,
        ]);
    }
}
