<?php

declare(strict_types=1);

namespace App\Actions\Checkout;

use App\DTOs\ShippingMethodDTO;
use App\Models\ShippingCity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class GetShippingMethods
{
    /**
     * Shipping methods available for a destination, resolved through the
     * `shipping_cities` scope (exact city > province > nationwide). One entry
     * per method, with the most specific cost/description winning.
     *
     * @return Collection<int, ShippingMethodDTO>
     */
    public function __invoke(int $cityId, int $provinceId): Collection
    {
        $rows = ShippingCity::query()
            ->where('status', true)
            ->where(function (Builder $query) use ($cityId, $provinceId): void {
                $query->where('city_id', $cityId)
                    ->orWhere(fn (Builder $q): Builder => $q->whereNull('city_id')->where('province_id', $provinceId))
                    ->orWhere(fn (Builder $q): Builder => $q->whereNull('city_id')->whereNull('province_id'));
            })
            ->whereHas('shippingMethod', fn (Builder $query): Builder => $query->where('status', true))
            ->with('shippingMethod.shippingLine')
            ->get();

        return $rows
            ->groupBy('shipping_method_id')
            ->map(fn (Collection $group): ShippingMethodDTO => $this->best($group, $cityId, $provinceId))
            ->values();
    }

    /**
     * @param  Collection<int, ShippingCity>  $group
     */
    private function best(Collection $group, int $cityId, int $provinceId): ShippingMethodDTO
    {
        /** @var ShippingCity $row */
        $row = $group
            ->sortByDesc(fn (ShippingCity $city): int => $this->specificity($city, $cityId, $provinceId))
            ->first();

        $method = $row->shippingMethod;

        return new ShippingMethodDTO(
            id: $method->id,
            name: $method->name,
            lineName: $method->shippingLine->name,
            description: $row->description,
            sendingDays: $row->sending_days,
            cost: $row->pay_on_delivery ? null : (int) ($row->amount ?? 0),
            payOnDelivery: $row->pay_on_delivery,
        );
    }

    private function specificity(ShippingCity $city, int $cityId, int $provinceId): int
    {
        if ($city->city_id === $cityId) {
            return 3;
        }

        if ($city->province_id === $provinceId) {
            return 2;
        }

        return 1;
    }
}
