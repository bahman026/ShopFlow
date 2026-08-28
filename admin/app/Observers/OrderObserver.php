<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\OrderStatusEnum;
use App\Exceptions\InsufficientInventoryException;
use App\Models\Order;
use App\Models\OrderVariety;
use App\Models\Variety;
use Illuminate\Support\Facades\DB;

class OrderObserver
{
    /**
     * Keeps `varieties.inventory` in step with the order's status.
     *
     * docs/ORDER.md: stock is consumed while an order is paid (through to
     * delivered) and released when it is canceled or returned. The storefront
     * upholds that for gateway payments in DecrementInventoryAndMarkPaid; this
     * upholds it for everything staff do in the panel — confirming a
     * card-to-card receipt, canceling an order, accepting a return.
     *
     * Only status *transitions between the two sets* act, so re-saving an order
     * without changing its status, or moving PAID -> SHIPPED, changes nothing.
     *
     * This observer is registered by the admin app only. The storefront has its
     * own Order model with no observer, so a Zarinpal payment still decrements
     * exactly once — in DecrementInventoryAndMarkPaid — and never twice.
     */
    public function updated(Order $order): void
    {
        if (! $order->wasChanged('status')) {
            return;
        }

        $before = $this->statusBefore($order);
        $after = $order->status;

        $wasHoldingStock = $before?->consumesStock() ?? false;
        $isHoldingStock = $after->consumesStock();

        if ($wasHoldingStock === $isHoldingStock) {
            return;
        }

        $isHoldingStock
            ? $this->consume($order)
            : $this->release($order);
    }

    /**
     * The status the order had before this save, or null when it cannot be
     * read (a freshly created order has nothing to compare against).
     */
    private function statusBefore(Order $order): ?OrderStatusEnum
    {
        $original = $order->getOriginal('status');

        if ($original instanceof OrderStatusEnum) {
            return $original;
        }

        return is_numeric($original) ? OrderStatusEnum::tryFrom((int) $original) : null;
    }

    /**
     * @throws InsufficientInventoryException when a line cannot be covered
     */
    private function consume(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            foreach ($this->lockedLines($order) as [$line, $variety]) {
                if ($variety === null) {
                    // The variety was deleted; the line keeps its price
                    // snapshot but there is no stock left to take.
                    continue;
                }

                if ($variety->inventory < $line->quantity) {
                    throw new InsufficientInventoryException(
                        varietyLabel: (string) $variety->id,
                        available: $variety->inventory,
                        required: $line->quantity,
                    );
                }

                $variety->decrement('inventory', $line->quantity);
            }
        });
    }

    private function release(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            foreach ($this->lockedLines($order) as [$line, $variety]) {
                $variety?->increment('inventory', $line->quantity);
            }
        });
    }

    /**
     * Order lines paired with their variety, locked for update and always
     * taken in the same order so two concurrent changes cannot deadlock.
     *
     * @return array<int, array{0: OrderVariety, 1: Variety|null}>
     */
    private function lockedLines(Order $order): array
    {
        $lines = $order->orderVarieties()
            ->whereNotNull('variety_id')
            ->orderBy('variety_id')
            ->get();

        $locked = [];

        foreach ($lines as $line) {
            /** @var OrderVariety $line */
            $locked[] = [
                $line,
                Variety::query()->whereKey($line->variety_id)->lockForUpdate()->first(),
            ];
        }

        return $locked;
    }
}
