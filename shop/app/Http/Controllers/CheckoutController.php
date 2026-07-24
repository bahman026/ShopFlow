<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Account\BuildAddressDTO;
use App\Actions\Cart\BuildCartSummary;
use App\Actions\Cart\GetCartLines;
use App\Actions\Cart\ResolveCartOwner;
use App\Actions\Checkout\GetShippingMethods;
use App\DTOs\ShippingMethodDTO;
use App\Models\Address;
use App\Models\Province;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(
        private ResolveCartOwner $owner,
        private GetShippingMethods $getShippingMethods,
    ) {}

    /**
     * Step 2: shipping. The customer picks an address (or adds one when none
     * exist) and a shipping method available for that destination.
     */
    public function shipping(Request $request, GetCartLines $getLines, BuildCartSummary $buildSummary, BuildAddressDTO $build): Response|RedirectResponse
    {
        $user = $this->user($request);
        $lines = $getLines(($this->owner)($request));

        if ($lines->isEmpty()) {
            return redirect()->route('cart')->with('status', 'سبد خرید شما خالی است.');
        }

        $addresses = Address::query()
            ->forUser($user->id)
            ->with('city.province')
            ->latest()
            ->get();

        $defaultAddress = $addresses->firstWhere('prime', true) ?? $addresses->first();
        $selectedAddressId = $request->session()->get('checkout.address_id')
            ?? ($defaultAddress === null ? null : $defaultAddress->id);

        $selectedAddress = $selectedAddressId === null
            ? null
            : $addresses->firstWhere('id', $selectedAddressId);

        return Inertia::render('Checkout/Shipping', [
            'summary' => $buildSummary($lines)->toArray(),
            'addresses' => $addresses->map(fn (Address $address): array => $build($address)->toArray())->all(),
            'selectedAddressId' => $selectedAddressId,
            'shippingMethods' => $this->methodsFor($selectedAddress),
            'selectedMethodId' => $request->session()->get('checkout.shipping_method_id'),
            'provinces' => Province::query()->orderBy('name')->get(['id', 'name'])->toArray(),
            'neshanMapKey' => config('services.neshan.map_key'),
        ]);
    }

    /**
     * Shipping methods for one of the user's addresses, as JSON. Used by the
     * shipping step to refresh the list when the chosen address changes.
     */
    public function methods(Request $request): JsonResponse
    {
        $user = $this->user($request);

        $validated = $request->validate([
            'address_id' => [
                'required',
                'integer',
                Rule::exists('addresses', 'id')->where('user_id', $user->id),
            ],
        ]);

        $address = Address::query()->forUser($user->id)->find($validated['address_id']);

        return response()->json(['methods' => $this->methodsFor($address)]);
    }

    public function storeShipping(Request $request): RedirectResponse
    {
        $user = $this->user($request);

        $validated = $request->validate([
            'address_id' => [
                'required',
                'integer',
                Rule::exists('addresses', 'id')->where('user_id', $user->id),
            ],
            'shipping_method_id' => ['required', 'integer'],
        ]);

        $address = Address::query()->forUser($user->id)->findOrFail($validated['address_id']);
        $available = collect($this->methodsFor($address));

        if (! $available->contains(fn (array $method): bool => $method['id'] === (int) $validated['shipping_method_id'])) {
            return back()->withErrors(['shipping_method_id' => 'روش ارسال انتخاب‌شده معتبر نیست.']);
        }

        $request->session()->put('checkout.address_id', (int) $validated['address_id']);
        $request->session()->put('checkout.shipping_method_id', (int) $validated['shipping_method_id']);

        return redirect()->route('checkout.payment');
    }

    /**
     * Step 3: payment. Placeholder until the gateway/receipt flow is built.
     */
    public function payment(Request $request, GetCartLines $getLines, BuildCartSummary $buildSummary, BuildAddressDTO $build): Response|RedirectResponse
    {
        $user = $this->user($request);
        $lines = $getLines(($this->owner)($request));

        if ($lines->isEmpty()) {
            return redirect()->route('cart')->with('status', 'سبد خرید شما خالی است.');
        }

        $addressId = $request->session()->get('checkout.address_id');
        $address = $addressId === null
            ? null
            : Address::query()->forUser($user->id)->with('city.province')->find($addressId);

        if ($address === null) {
            return redirect()->route('checkout.shipping')->with('status', 'لطفاً نشانی ارسال را انتخاب کنید.');
        }

        $methodId = $request->session()->get('checkout.shipping_method_id');
        $method = collect($this->methodsFor($address))
            ->firstWhere('id', $methodId === null ? 0 : (int) $methodId);

        if ($method === null) {
            return redirect()->route('checkout.shipping')->with('status', 'لطفاً روش ارسال را انتخاب کنید.');
        }

        return Inertia::render('Checkout/Payment', [
            'summary' => $buildSummary($lines)->toArray(),
            'address' => $build($address)->toArray(),
            'shippingMethod' => $method,
        ]);
    }

    /**
     * Shipping methods (as arrays) available for an address, or an empty list
     * when there is no address yet.
     *
     * @return array<int, array<string, mixed>>
     */
    private function methodsFor(?Address $address): array
    {
        if ($address === null) {
            return [];
        }

        $city = $address->city;

        return ($this->getShippingMethods)($city->id, $city->province_id)
            ->map(fn (ShippingMethodDTO $method): array => $method->toArray())
            ->all();
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
