<?php

declare(strict_types=1);

use App\Enums\TransactionPortEnum;
use App\Enums\TransactionStatusEnum;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;

it('belongs to a user.', function (): void {
    $transaction = Transaction::factory()->create();

    expect($transaction->user)->toBeInstanceOf(User::class);
});

it('casts port, status and paid_at.', function (): void {
    $transaction = Transaction::factory()->create([
        'port' => TransactionPortEnum::MELLAT,
        'status' => TransactionStatusEnum::SUCCESS,
        'paid_at' => now(),
    ]);

    expect($transaction->refresh()->port)->toBe(TransactionPortEnum::MELLAT)
        ->and($transaction->status)->toBe(TransactionStatusEnum::SUCCESS)
        ->and($transaction->paid_at)->not->toBeNull();
});

it('can optionally link an order.', function (): void {
    $order = Order::factory()->create();
    $transaction = Transaction::factory()->create(['order_id' => $order->id]);

    expect($transaction->order)->toBeInstanceOf(Order::class)
        ->and($transaction->order->id)->toBe($order->id);
});

it('keeps the transaction but nulls order_id when the order is deleted.', function (): void {
    $order = Order::factory()->create();
    $transaction = Transaction::factory()->create(['order_id' => $order->id]);

    $order->delete();

    expect($transaction->refresh()->order_id)->toBeNull();
});
