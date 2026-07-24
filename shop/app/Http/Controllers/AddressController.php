<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Account\BuildAddressDTO;
use App\Actions\Account\ReverseGeocode;
use App\Actions\Account\StaticMap;
use App\Actions\Account\StoreUserAddress;
use App\Actions\Account\UpdateUserAddress;
use App\Actions\Auth\NormalizeMobile;
use App\Models\Address;
use App\Models\City;
use App\Models\Province;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AddressController extends Controller
{
    public function index(Request $request, BuildAddressDTO $build): Response
    {
        $user = $this->user($request);

        $addresses = Address::query()
            ->forUser($user->id)
            ->with('city.province')
            ->latest()
            ->get()
            ->map(fn (Address $address): array => $build($address)->toArray())
            ->all();

        return Inertia::render('Account/Addresses/Index', [
            'addresses' => $addresses,
            'provinces' => Province::query()->orderBy('name')->get(['id', 'name'])->toArray(),
            'neshanMapKey' => config('services.neshan.map_key'),
        ]);
    }

    public function store(Request $request, NormalizeMobile $normalize, StoreUserAddress $store): RedirectResponse
    {
        $user = $this->user($request);
        $data = $this->validated($request, $normalize);

        // The first address a user adds becomes their default automatically.
        $data['prime'] = $data['prime'] || ! Address::query()->forUser($user->id)->exists();

        $store($user, $data);

        return back()->with('status', 'نشانی با موفقیت ثبت شد.');
    }

    public function update(Request $request, Address $address, NormalizeMobile $normalize, UpdateUserAddress $update): RedirectResponse
    {
        $this->ensureOwner($request, $address);

        $update($address, $this->validated($request, $normalize));

        return back()->with('status', 'نشانی با موفقیت ویرایش شد.');
    }

    public function setPrimary(Request $request, Address $address): RedirectResponse
    {
        $this->ensureOwner($request, $address);

        // Saving with prime=true demotes the user's other addresses (model hook).
        $address->update(['prime' => true]);

        return back()->with('status', 'نشانی پیش‌فرض تغییر کرد.');
    }

    public function destroy(Request $request, Address $address): RedirectResponse
    {
        $this->ensureOwner($request, $address);

        // Soft delete only: orders reference addresses, so the row must survive
        // for history. It just leaves the customer's active list.
        $wasPrime = $address->prime;
        $address->delete();

        // Keep a default selectable: promote the newest remaining address.
        if ($wasPrime) {
            Address::query()
                ->forUser($address->user_id)
                ->latest()
                ->first()
                ?->update(['prime' => true]);
        }

        return back()->with('status', 'نشانی حذف شد.');
    }

    public function cities(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'province_id' => ['required', 'integer', Rule::exists('provinces', 'id')],
        ]);

        $cities = City::query()
            ->where('province_id', $validated['province_id'])
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($cities);
    }

    public function reverse(Request $request, ReverseGeocode $reverse): JsonResponse
    {
        $validated = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        return response()->json([
            'address' => $reverse((float) $validated['lat'], (float) $validated['lng']),
        ]);
    }

    public function staticMap(Request $request, StaticMap $static): HttpResponse
    {
        $validated = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'zoom' => ['nullable', 'integer', 'between:5,19'],
        ]);

        $image = $static(
            (float) $validated['lat'],
            (float) $validated['lng'],
            (int) ($validated['zoom'] ?? 15),
        );

        if ($image === null) {
            abort(404);
        }

        return response($image['body'], 200)
            ->header('Content-Type', $image['contentType'])
            ->header('Cache-Control', 'private, max-age=86400');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, NormalizeMobile $normalize): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'city_id' => ['required', 'integer', Rule::exists('cities', 'id')],
            'address' => ['required', 'string', 'max:1000'],
            'plate' => ['nullable', 'string', 'max:50'],
            'unit' => ['nullable', 'string', 'max:50'],
            'postal_code' => ['required', 'string'],
            'phone' => ['required', 'string'],
            'note' => ['nullable', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'prime' => ['boolean'],
        ], [
            'required' => 'وارد کردن :attribute الزامی است.',
            'string' => ':attribute باید متن باشد.',
            'integer' => ':attribute نامعتبر است.',
            'numeric' => ':attribute نامعتبر است.',
            'exists' => ':attribute انتخاب‌شده معتبر نیست.',
            'max' => ':attribute نباید بیشتر از :max نویسه باشد.',
            'between' => ':attribute خارج از محدوده مجاز است.',
            'boolean' => ':attribute نامعتبر است.',
        ], [
            'name' => 'عنوان نشانی',
            'city_id' => 'شهر',
            'address' => 'نشانی',
            'plate' => 'پلاک',
            'unit' => 'واحد',
            'postal_code' => 'کد پستی',
            'phone' => 'شماره موبایل',
            'note' => 'توضیحات',
            'latitude' => 'موقعیت مکانی',
            'longitude' => 'موقعیت مکانی',
        ]);

        $phone = $normalize($validated['phone']);

        if ($phone === null) {
            throw ValidationException::withMessages([
                'phone' => 'شماره موبایل معتبر نیست.',
            ]);
        }

        $postal = $this->toEnglishDigits($validated['postal_code']);

        if (preg_match('/^\d{10}$/', $postal) !== 1) {
            throw ValidationException::withMessages([
                'postal_code' => 'کد پستی باید ۱۰ رقم باشد.',
            ]);
        }

        return [
            'name' => $validated['name'],
            'city_id' => $validated['city_id'],
            'address' => $validated['address'],
            'plate' => $validated['plate'] ?? null,
            'unit' => $validated['unit'] ?? null,
            'postal_code' => $postal,
            'phone' => $phone,
            'note' => $validated['note'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'prime' => (bool) ($validated['prime'] ?? false),
        ];
    }

    private function toEnglishDigits(string $value): string
    {
        return strtr($value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
    }

    private function ensureOwner(Request $request, Address $address): void
    {
        if ($address->user_id !== $this->user($request)->id) {
            abort(403);
        }
    }

    private function user(Request $request): User
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        return $user;
    }
}
