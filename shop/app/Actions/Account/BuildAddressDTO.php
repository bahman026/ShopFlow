<?php

declare(strict_types=1);

namespace App\Actions\Account;

use App\DTOs\AddressDTO;
use App\Models\Address;
use App\Support\AddressDescription;

class BuildAddressDTO
{
    public function __invoke(Address $address): AddressDTO
    {
        $parts = AddressDescription::decode($address->description);
        $city = $address->city;
        $province = $city->province;

        return new AddressDTO(
            id: $address->id,
            name: $address->name,
            phone: $address->phone,
            postalCode: $address->postal_code,
            address: $address->address,
            plate: $parts['plate'],
            unit: $parts['unit'],
            note: $parts['note'],
            latitude: $address->latitude,
            longitude: $address->longitude,
            cityId: $address->city_id,
            cityName: $city->name,
            provinceId: $province->id,
            provinceName: $province->name,
            prime: $address->prime,
        );
    }
}
