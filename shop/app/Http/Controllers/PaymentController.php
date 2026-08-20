<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Cart\BuildCartSummary;
use App\Actions\Cart\GetCartLines;
use App\Actions\Cart\ResolveCartOwner;
use App\Actions\Checkout\BuildOrderDTO;
use App\Actions\Checkout\CompleteCheckoutPayment;
use App\Actions\Checkout\GetShippingMethods;
use App\Actions\Checkout\StartCheckoutPayment;
use App\Actions\Checkout\ValidateCartStock;
use App\Actions\Coupon\ResolveCartCoupon;
use App\DTOs\ShippingMethodDTO;
use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class PaymentController extends Controller
{
    public function __construct(
        private ResolveCartOwner $owner,
        private GetShippingMethods $getShippingMethods,
        private StartCheckoutPayment $startPayment,
        private CompleteCheckoutPayment $completePayment,
        private BuildOrderDTO $buildOrderDTO,
        private ResolveCartCoupon $resolveCoupon,
    ) {}

    /**
     * Create the pending order + Zarinpal payment session, then send the
     * customer to Zarinpal's payment page (an external redirect, handled by
     * Inertia::location so the client does a real navigation).
     */
    public function initiate(Request $request, GetCartLines $getLines, BuildCartSummary $buildSummary, ValidateCartStock $validateStock): RedirectResponse|SymfonyResponse
    {
        $user = $this->user($request);
        $lines = $getLines(($this->owner)($request));

        if ($lines->isEmpty()) {
            return redirect()->route('cart')->with('status', trans('messages.cart.empty'));
        }

        // Stock can change between adding to cart and reaching payment; never
        // open a Zarinpal payment session for something no longer available.
        if (! $validateStock($lines)) {
            return redirect()->route('cart')->with('status', trans('messages.checkout.stock_changed'));
        }

        $address = $this->resolveAddress($request, $user);

        if ($address === null) {
            return redirect()->route('checkout.shipping')->with('status', trans('messages.checkout.choose_address'));
        }

        $method = $this->resolveMethod($request, $address);

        if ($method === null) {
            return redirect()->route('checkout.shipping')->with('status', trans('messages.checkout.choose_method'));
        }

        // Re-validate the applied code here, not just on the cart: this is the
        // number the customer is actually charged.
        $preview = ($this->resolveCoupon)($request, $lines);
        $coupon = $preview['coupon'];

        // A code that has stopped working since the cart must never be dropped
        // silently — that would charge more than the customer was shown.
        if ($coupon === null && $this->resolveCoupon->isApplied($request)) {
            $this->resolveCoupon->forget($request);

            return redirect()->route('cart')
                ->with('status', $preview['error'] ?? trans('messages.cart.coupon.invalid'));
        }

        if ($coupon?->freeShipping === true) {
            $method = new ShippingMethodDTO(
                id: $method->id,
                name: $method->name,
                lineName: $method->lineName,
                description: $method->description,
                sendingDays: $method->sendingDays,
                cost: 0,
                payOnDelivery: $method->payOnDelivery,
            );
        }

        $summary = $buildSummary($lines, $coupon === null ? 0 : $coupon->discount);

        $url = ($this->startPayment)(
            $user,
            $lines,
            $address,
            $method,
            $summary,
            route('checkout.callback'),
            (string) $request->ip(),
            $coupon,
        );

        if ($url === null) {
            return back()->with('status', trans('messages.payment.gateway_error'));
        }

        return Inertia::location($url);
    }

    /**
     * Zarinpal's return redirect. Looked up by `Authority`, not session, since
     * that's the only value guaranteed to survive the round-trip.
     */
    public function callback(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'Authority' => ['required', 'string'],
            'Status' => ['required', 'string'],
        ]);

        $order = ($this->completePayment)($validated['Authority'], $validated['Status']);

        if ($order === null) {
            return redirect()->route('checkout.payment')->with('status', trans('messages.payment.failed'));
        }

        $request->session()->forget(['checkout.address_id', 'checkout.shipping_method_id']);
        // The code has been spent on this order; a fresh cart starts clean.
        $this->resolveCoupon->forget($request);

        return redirect()->route('checkout.confirmation', $order);
    }

    public function confirmation(Request $request, Order $order): Response
    {
        if ($order->user_id !== $this->user($request)->id) {
            abort(403);
        }

        return Inertia::render('Checkout/Confirmation', [
            'order' => ($this->buildOrderDTO)($order)->toArray(),
        ]);
    }

    private function resolveAddress(Request $request, User $user): ?Address
    {
        $addressId = $request->session()->get('checkout.address_id');

        if ($addressId === null) {
            return null;
        }

        return Address::query()->forUser($user->id)->with('city.province')->find($addressId);
    }

    private function resolveMethod(Request $request, Address $address): ?ShippingMethodDTO
    {
        $methodId = $request->session()->get('checkout.shipping_method_id');

        if ($methodId === null) {
            return null;
        }

        $city = $address->city;

        return ($this->getShippingMethods)($city->id, $city->province_id)
            ->firstWhere('id', (int) $methodId);
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
