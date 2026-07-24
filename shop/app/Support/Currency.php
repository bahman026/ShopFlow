<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Every price in this codebase (Variety::price, cart/order totals) is stored
 * in Toman. Zarinpal's API works in Rial, so the conversion lives here once,
 * used only at the Zarinpal HTTP boundary, so a request and its verify call
 * can never drift apart.
 */
class Currency
{
    public static function tomanToRial(int $toman): int
    {
        return $toman * 10;
    }
}
