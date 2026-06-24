<?php

declare(strict_types=1);

use App\Models\Order;
use App\Models\OrderNote;
use App\Models\User;

it('belongs to an order and an author.', function (): void {
    $note = OrderNote::factory()->create();

    expect($note->order)->toBeInstanceOf(Order::class)
        ->and($note->user)->toBeInstanceOf(User::class);
});

it('is deleted when its order is deleted.', function (): void {
    $note = OrderNote::factory()->create();

    $note->order->delete();

    $this->assertModelMissing($note);
});

it('is exposed through the order relation.', function (): void {
    $order = Order::factory()->create();
    OrderNote::factory()->count(2)->create(['order_id' => $order->id]);

    expect($order->orderNotes)->toHaveCount(2);
});
