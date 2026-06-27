<?php

declare(strict_types=1);

namespace App\Actions\Account;

use App\Models\Address;
use App\Models\User;
use App\Support\AddressDescription;

class StoreUserAddress
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __invoke(User $user, array $data): Address
    {
        return Address::create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'phone' => $data['phone'],
            'postal_code' => $data['postal_code'],
            'address' => $data['address'],
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'description' => AddressDescription::encode($data['plate'], $data['unit'], $data['note']),
            'city_id' => $data['city_id'],
            'prime' => $data['prime'],
        ]);
    }
}
