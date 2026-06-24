<?php

declare(strict_types=1);

use App\Filament\Resources\AddressResource;
use App\Models\Address;
use App\Models\City;
use App\Models\User;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    login();
});

it('can render index page of the address resource.', function () {
    get(AddressResource::getUrl())->assertOk();
});

it('can list addresses in the table.', function () {
    $addresses = Address::factory()->count(5)->create();

    livewire(AddressResource\Pages\ListAddresses::class)
        ->assertCanSeeTableRecords($addresses);
});

it('can render edit address page.', function () {
    $address = Address::factory()->create();

    get(AddressResource::getUrl('edit', [
        'record' => $address,
    ]))->assertOk();
});

it('can create address model.', function () {
    $user = User::factory()->create();
    $city = City::factory()->create();

    livewire(AddressResource\Pages\CreateAddress::class)
        ->fillForm([
            'user_id' => $user->id,
            'city_id' => $city->id,
            'name' => 'Home',
            'phone' => '02112345678',
            'postal_code' => '1234567890',
            'address' => 'Some street',
            'prime' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Address::class, [
        'user_id' => $user->id,
        'name' => 'Home',
        'prime' => true,
    ]);
});

it('creates a new primary address on edit when the edited one was primary.', function () {
    $address = Address::factory()->create([
        'name' => 'Old name',
        'prime' => true,
    ]);

    livewire(AddressResource\Pages\EditAddress::class, [
        'record' => $address->getRouteKey(),
    ])
        ->fillForm([
            'name' => 'New name',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    // Original record is preserved exactly and no longer primary.
    $original = Address::findOrFail($address->id);
    expect($original->name)->toBe('Old name')
        ->and($original->prime)->toBeFalse();

    // A brand-new primary address was created for the same user.
    $new = Address::query()
        ->where('user_id', $address->user_id)
        ->where('name', 'New name')
        ->firstOrFail();
    expect($new->id)->not->toBe($address->id)
        ->and($new->prime)->toBeTrue();

    expect(Address::query()->where('user_id', $address->user_id)->count())->toBe(2);
});

it('creates a new non-primary address on edit when the edited one was not primary.', function () {
    $user = User::factory()->create();
    $primary = Address::factory()->prime()->create(['user_id' => $user->id]);
    $address = Address::factory()->create([
        'user_id' => $user->id,
        'name' => 'Old name',
        'prime' => false,
    ]);

    livewire(AddressResource\Pages\EditAddress::class, [
        'record' => $address->getRouteKey(),
    ])
        ->fillForm([
            'name' => 'New name',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    // The new address is created but stays non-primary.
    $new = Address::query()
        ->where('user_id', $user->id)
        ->where('name', 'New name')
        ->firstOrFail();
    expect($new->id)->not->toBe($address->id)
        ->and($new->prime)->toBeFalse();

    // The existing primary address is untouched.
    expect($primary->refresh()->prime)->toBeTrue();
    expect(Address::query()->where('user_id', $user->id)->count())->toBe(3);
});
