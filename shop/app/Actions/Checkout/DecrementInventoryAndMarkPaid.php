<?php

declare(strict_types=1);

namespace App\Actions\Checkout;

use App\Enums\OrderStatusEnum;
use App\Enums\TransactionStatusEnum;
use App\Models\Order;
use App\Models\OrderVariety;
use App\Models\Transaction;
use App\Models\Variety;
use Illuminate\Support\Facades\DB;

class DecrementInventoryAndMarkPaid
{
    /**
     * Strategy A (docs/ORDER.md): inventory is decremented only here, on
     * successful payment, inside one row-locked transaction. Returns false
     * (and rolls back, touching nothing) if any line no longer has enough
     * stock — the rare race where two people paid for the last unit.
     */
    public function __invoke(Order $order, Transaction $transaction, ?string $refId): bool
    {
        return DB::transaction(function () use ($order, $transaction, $refId): bool {
            $lines = $order->orderVarieties()
                ->whereNotNull('variety_id')
                ->orderBy('variety_id')
                ->get();

            foreach ($lines as $line) {
                /** @var OrderVariety $line */
                $variety = Variety::query()->whereKey($line->variety_id)->lockForUpdate()->first();

                if ($variety === null || $variety->inventory < $line->quantity) {
                    return false;
                }

                $variety->decrement('inventory', $line->quantity);
            }

            $order->update(['status' => OrderStatusEnum::PAID]);

            $transaction->update([
                'status' => TransactionStatusEnum::SUCCESS,
                'ref_id' => $refId,
                'paid_at' => now(),
                'result_code' => '100',
            ]);

            return true;
        });
    }
}
