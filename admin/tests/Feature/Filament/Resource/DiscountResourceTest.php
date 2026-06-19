<?php

declare(strict_types=1);

use App\Filament\Resources\DiscountResource;
use App\Models\Discount;
use App\Models\Variety;
use Filament\Actions\DeleteAction;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    login();
});

it('can render index page of the discount resource.', function () {
    get(DiscountResource::getUrl())->assertOk();
});

it('can list discounts in the table.', function () {
    $discounts = Discount::factory()->count(5)->create();

    livewire(DiscountResource\Pages\ListDiscounts::class)
        ->assertCanSeeTableRecords($discounts);
});

it('can render edit discount page.', function () {
    $discount = Discount::factory()->create();

    get(DiscountResource::getUrl('edit', [
        'record' => $discount,
    ]))->assertOk();
});

it('can update discount model.', function () {
    $discount = Discount::factory()->create();
    $newDiscount = Discount::factory()->make();
    $variety = Variety::factory()->create();

    livewire(DiscountResource\Pages\EditDiscount::class, [
        'record' => $discount->getRouteKey(),
    ])
        ->fillForm([
            'variety_id' => $variety->id,
            'quantity' => $newDiscount->quantity,
            'priority' => $newDiscount->priority,
            'is_percent' => $newDiscount->is_percent,
            'amount' => $newDiscount->amount,
            'sold' => $newDiscount->sold,
            'max_sell' => $newDiscount->max_sell,
            'max_sell_by_user' => $newDiscount->max_sell_by_user,
            'is_for' => $newDiscount->is_for->value,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($discount->refresh())
        ->variety_id->toBe($variety->id)
        ->quantity->toBe($newDiscount->quantity)
        ->priority->toBe($newDiscount->priority)
        ->is_percent->toBe($newDiscount->is_percent)
        ->amount->toBe($newDiscount->amount)
        ->sold->toBe($newDiscount->sold)
        ->max_sell->toBe($newDiscount->max_sell)
        ->max_sell_by_user->toBe($newDiscount->max_sell_by_user)
        ->is_for->toBe($newDiscount->is_for);
});

it('can create discount model.', function () {
    $variety = Variety::factory()->create();
    $newDiscount = Discount::factory()->make(['variety_id' => $variety->id]);

    livewire(DiscountResource\Pages\CreateDiscount::class)
        ->fillForm([
            'variety_id' => $variety->id,
            'quantity' => $newDiscount->quantity,
            'priority' => $newDiscount->priority,
            'is_percent' => $newDiscount->is_percent,
            'amount' => $newDiscount->amount,
            'sold' => $newDiscount->sold,
            'max_sell' => $newDiscount->max_sell,
            'max_sell_by_user' => $newDiscount->max_sell_by_user,
            'is_for' => $newDiscount->is_for->value,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Discount::class, [
        'variety_id' => $variety->id,
        'quantity' => $newDiscount->quantity,
        'priority' => $newDiscount->priority,
        'is_percent' => (int) $newDiscount->is_percent,
        'amount' => $newDiscount->amount,
        'sold' => $newDiscount->sold,
        'max_sell' => $newDiscount->max_sell,
        'max_sell_by_user' => $newDiscount->max_sell_by_user,
        'is_for' => $newDiscount->is_for->value,
    ]);
});

it('can delete discount model.', function () {
    $discount = Discount::factory()->create();

    livewire(DiscountResource\Pages\EditDiscount::class, [
        'record' => $discount->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($discount);
});
