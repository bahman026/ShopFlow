<?php

declare(strict_types=1);

namespace App\Actions\Account;

use App\Models\Address;
use App\Support\AddressDescription;
use Illuminate\Support\Facades\DB;

class UpdateUserAddress
{
    /**
     * Addresses are immutable history (orders reference them), so editing never
     * mutates a row. We create a NEW address from the edited data, inheriting
     * the old one's primary status, and soft-delete the old record so it leaves
     * the active list while staying available for order history.
     *
     * @param  array<string, mixed>  $data
     */
    public function __invoke(Address $old, array $data): Address
    {
        return DB::transaction(function () use ($old, $data): Address {
            $new = Address::create([
                'user_id' => $old->user_id,
                'name' => $data['name'],
                'phone' => $data['phone'],
                'postal_code' => $data['postal_code'],
                'address' => $data['address'],
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'description' => AddressDescription::encode($data['plate'], $data['unit'], $data['note']),
                'city_id' => $data['city_id'],
                'prime' => $data['prime'] || $old->prime,
            ]);

            $old->delete();

            return $new;
        });
    }
}
