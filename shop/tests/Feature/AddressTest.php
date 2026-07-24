<?php

declare(strict_types=1);

use App\Models\Address;
use App\Models\City;
use App\Models\Province;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia;

function makeCity(string $province = 'تهران'): City
{
    $model = Province::create(['name' => $province.' '.uniqid()]);

    return City::create(['name' => 'شهر', 'province_id' => $model->id]);
}

function addressPayload(City $city, array $overrides = []): array
{
    return array_merge([
        'name' => 'منزل',
        'city_id' => $city->id,
        'address' => 'خیابان آزادی، کوچه اول',
        'plate' => '12',
        'unit' => '3',
        'postal_code' => '1234567890',
        'phone' => '09121112233',
        'latitude' => 35.7,
        'longitude' => 51.4,
        'prime' => false,
    ], $overrides);
}

it('redirects guests to login', function (): void {
    $this->get('/account/addresses')->assertRedirect('/login');
});

it('lists the user addresses', function (): void {
    $user = User::factory()->create();
    $city = makeCity();
    Address::create([
        'user_id' => $user->id,
        'name' => 'منزل',
        'phone' => '09121112233',
        'postal_code' => '1234567890',
        'address' => 'خیابان آزادی',
        'city_id' => $city->id,
        'prime' => true,
    ]);

    $this->actingAs($user)
        ->get('/account/addresses')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Account/Addresses/Index')
            ->has('addresses', 1)
            ->where('addresses.0.name', 'منزل')
        );
});

it('stores a new address and makes the first one primary', function (): void {
    $user = User::factory()->create();
    $city = makeCity();

    $this->actingAs($user)
        ->post('/account/addresses', addressPayload($city))
        ->assertRedirect()
        ->assertSessionHas('status');

    $address = Address::query()->where('user_id', $user->id)->firstOrFail();
    expect($address->prime)->toBeTrue()
        ->and($address->address)->toBe('خیابان آزادی، کوچه اول');
});

it('demotes the previous primary when a new primary is added', function (): void {
    $user = User::factory()->create();
    $city = makeCity();

    $this->actingAs($user)->post('/account/addresses', addressPayload($city));
    $first = Address::query()->where('user_id', $user->id)->firstOrFail();

    $this->actingAs($user)->post('/account/addresses', addressPayload($city, [
        'address' => 'نشانی دوم',
        'prime' => true,
    ]));

    expect($first->fresh()->prime)->toBeFalse();
    expect(Address::query()->where('user_id', $user->id)->where('prime', true)->count())->toBe(1);
});

it('normalizes persian digits in phone and postal code', function (): void {
    $user = User::factory()->create();
    $city = makeCity();

    $this->actingAs($user)->post('/account/addresses', addressPayload($city, [
        'phone' => '۰۹۱۲۱۱۱۲۲۳۳',
        'postal_code' => '۱۲۳۴۵۶۷۸۹۰',
    ]));

    $this->assertDatabaseHas('addresses', [
        'user_id' => $user->id,
        'phone' => '09121112233',
        'postal_code' => '1234567890',
    ]);
});

it('validates required fields', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/account/addresses', [])
        ->assertSessionHasErrors(['name', 'city_id', 'address', 'postal_code', 'phone']);
});

it('rejects an invalid postal code length', function (): void {
    $user = User::factory()->create();
    $city = makeCity();

    $this->actingAs($user)
        ->post('/account/addresses', addressPayload($city, ['postal_code' => '123']))
        ->assertSessionHasErrors('postal_code');
});

it('edits an address by creating a new record and soft-deleting the old', function (): void {
    $user = User::factory()->create();
    $city = makeCity();

    $this->actingAs($user)->post('/account/addresses', addressPayload($city));
    $old = Address::query()->where('user_id', $user->id)->firstOrFail();

    $this->actingAs($user)
        ->put('/account/addresses/'.$old->id, addressPayload($city, ['address' => 'نشانی ویرایش‌شده']))
        ->assertRedirect();

    $this->assertSoftDeleted('addresses', ['id' => $old->id]);

    $new = Address::query()->where('user_id', $user->id)->firstOrFail();
    expect($new->id)->not->toBe($old->id)
        ->and($new->address)->toBe('نشانی ویرایش‌شده')
        ->and($new->prime)->toBeTrue();
});

it('sets an address as primary and demotes the previous one', function (): void {
    $user = User::factory()->create();
    $city = makeCity();

    $this->actingAs($user)->post('/account/addresses', addressPayload($city));
    $this->actingAs($user)->post('/account/addresses', addressPayload($city, ['name' => 'محل کار']));

    $first = Address::query()->forUser($user->id)->where('prime', true)->firstOrFail();
    $second = Address::query()->forUser($user->id)->where('prime', false)->firstOrFail();

    $this->actingAs($user)
        ->put('/account/addresses/'.$second->id.'/primary')
        ->assertRedirect()
        ->assertSessionHas('status');

    $this->assertDatabaseHas('addresses', ['id' => $second->id, 'prime' => true]);
    $this->assertDatabaseHas('addresses', ['id' => $first->id, 'prime' => false]);
});

it('forbids setting an address primary for someone else', function (): void {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $city = makeCity();

    $address = Address::create([
        'user_id' => $owner->id,
        'name' => 'منزل',
        'phone' => '09121112233',
        'postal_code' => '1234567890',
        'address' => 'خیابان آزادی',
        'city_id' => $city->id,
        'prime' => true,
    ]);

    $this->actingAs($other)
        ->put('/account/addresses/'.$address->id.'/primary')
        ->assertForbidden();
});

it('soft-deletes an address and promotes another to primary', function (): void {
    $user = User::factory()->create();
    $city = makeCity();

    $this->actingAs($user)->post('/account/addresses', addressPayload($city));
    $this->actingAs($user)->post('/account/addresses', addressPayload($city, ['name' => 'محل کار']));

    $primary = Address::query()->forUser($user->id)->where('prime', true)->firstOrFail();
    $other = Address::query()->forUser($user->id)->where('prime', false)->firstOrFail();

    $this->actingAs($user)
        ->delete('/account/addresses/'.$primary->id)
        ->assertRedirect()
        ->assertSessionHas('status');

    $this->assertSoftDeleted('addresses', ['id' => $primary->id]);
    $this->assertDatabaseHas('addresses', ['id' => $other->id, 'prime' => true]);
});

it('forbids deleting an address owned by someone else', function (): void {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $city = makeCity();

    $address = Address::create([
        'user_id' => $owner->id,
        'name' => 'منزل',
        'phone' => '09121112233',
        'postal_code' => '1234567890',
        'address' => 'خیابان آزادی',
        'city_id' => $city->id,
        'prime' => true,
    ]);

    $this->actingAs($other)
        ->delete('/account/addresses/'.$address->id)
        ->assertForbidden();
});

it('forbids editing an address owned by someone else', function (): void {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $city = makeCity();

    $address = Address::create([
        'user_id' => $owner->id,
        'name' => 'منزل',
        'phone' => '09121112233',
        'postal_code' => '1234567890',
        'address' => 'خیابان آزادی',
        'city_id' => $city->id,
        'prime' => true,
    ]);

    $this->actingAs($other)
        ->put('/account/addresses/'.$address->id, addressPayload($city))
        ->assertForbidden();
});

it('returns cities for a province', function (): void {
    $user = User::factory()->create();
    $province = Province::create(['name' => 'استان تست '.uniqid()]);
    City::create(['name' => 'شهر الف', 'province_id' => $province->id]);
    City::create(['name' => 'شهر ب', 'province_id' => $province->id]);

    $this->actingAs($user)
        ->getJson('/account/addresses-cities?province_id='.$province->id)
        ->assertOk()
        ->assertJsonCount(2);
});

it('reverse geocodes coordinates through neshan', function (): void {
    config()->set('services.neshan.service_key', 'test-key');
    Http::fake([
        'api.neshan.org/*' => Http::response(['formatted_address' => 'تهران، خیابان آزادی'], 200),
    ]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson('/account/addresses-reverse?lat=35.7&lng=51.4')
        ->assertOk()
        ->assertJson(['address' => 'تهران، خیابان آزادی']);
});
