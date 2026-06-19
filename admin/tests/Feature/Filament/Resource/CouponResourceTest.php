<?php

declare(strict_types=1);

use App\Filament\Resources\CouponResource;
use App\Models\Coupon;
use Filament\Actions\DeleteAction;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    login();
});

it('can render index page of the coupon resource.', function () {
    get(CouponResource::getUrl())->assertOk();
});

it('can list coupons in the table.', function () {
    $coupons = Coupon::factory()->count(5)->create();

    livewire(CouponResource\Pages\ListCoupons::class)
        ->assertCanSeeTableRecords($coupons);
});

it('can render edit coupon page.', function () {
    $coupon = Coupon::factory()->create();

    get(CouponResource::getUrl('edit', [
        'record' => $coupon,
    ]))->assertOk();
});

it('can update coupon model.', function () {
    $coupon = Coupon::factory()->create();
    $newCoupon = Coupon::factory()->make();

    livewire(CouponResource\Pages\EditCoupon::class, [
        'record' => $coupon->getRouteKey(),
    ])
        ->fillForm([
            'name' => $newCoupon->name,
            'code' => $newCoupon->code,
            'amount' => $newCoupon->amount,
            'min_price' => $newCoupon->min_price,
            'max_discount' => $newCoupon->max_discount,
            'total_used' => $newCoupon->total_used,
            'total_uses' => $newCoupon->total_uses,
            'is_percent' => $newCoupon->is_percent,
            'shipping' => $newCoupon->shipping,
            'status' => $newCoupon->status->value,
            'is_for' => $newCoupon->is_for->value,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($coupon->refresh())
        ->name->toBe($newCoupon->name)
        ->code->toBe($newCoupon->code)
        ->amount->toBe($newCoupon->amount)
        ->is_percent->toBe($newCoupon->is_percent)
        ->shipping->toBe($newCoupon->shipping)
        ->status->toBe($newCoupon->status)
        ->is_for->toBe($newCoupon->is_for);
});

it('can create coupon model.', function () {
    $newCoupon = Coupon::factory()->make();

    livewire(CouponResource\Pages\CreateCoupon::class)
        ->fillForm([
            'name' => $newCoupon->name,
            'code' => $newCoupon->code,
            'amount' => $newCoupon->amount,
            'min_price' => $newCoupon->min_price,
            'max_discount' => $newCoupon->max_discount,
            'total_used' => $newCoupon->total_used,
            'total_uses' => $newCoupon->total_uses,
            'is_percent' => $newCoupon->is_percent,
            'shipping' => $newCoupon->shipping,
            'status' => $newCoupon->status->value,
            'is_for' => $newCoupon->is_for->value,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Coupon::class, [
        'name' => $newCoupon->name,
        'code' => $newCoupon->code,
        'amount' => $newCoupon->amount,
        'total_used' => $newCoupon->total_used,
        'total_uses' => $newCoupon->total_uses,
        'is_percent' => (int) $newCoupon->is_percent,
        'shipping' => (int) $newCoupon->shipping,
        'status' => $newCoupon->status->value,
        'is_for' => $newCoupon->is_for->value,
    ]);
});

it('can delete coupon model.', function () {
    $coupon = Coupon::factory()->create();

    livewire(CouponResource\Pages\EditCoupon::class, [
        'record' => $coupon->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($coupon);
});
