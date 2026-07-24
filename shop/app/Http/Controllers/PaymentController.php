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
            return redirect()->route('cart')->with('status', 'سبد خرید شما خالی است.');
        }

        // Stock can change between adding to cart and reaching payment; never
        // open a Zarinpal payment session for something no longer available.
        if (! $validateStock($lines)) {
            return redirect()->route('cart')->with('status', 'موجودی برخی از کالاهای سبد خرید شما تغییر کرده است. لطفاً سبد خرید را بررسی کنید.');
        }

        $address = $this->resolveAddress($request, $user);

        if ($address === null) {
            return redirect()->route('checkout.shipping')->with('status', 'لطفاً نشانی ارسال را انتخاب کنید.');
        }

        $method = $this->resolveMethod($request, $address);

        if ($method === null) {
            return redirect()->route('checkout.shipping')->with('status', 'لطفاً روش ارسال را انتخاب کنید.');
        }

        $summary = $buildSummary($lines);

        $url = ($this->startPayment)(
            $user,
            $lines,
            $address,
            $method,
            $summary,
            route('checkout.callback'),
            (string) $request->ip(),
        );

        if ($url === null) {
            return back()->with('status', 'در اتصال به درگاه پرداخت خطایی رخ داد. لطفاً دوباره تلاش کنید.');
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
            return redirect()->route('checkout.payment')->with('status', 'پرداخت ناموفق بود.');
        }

        $request->session()->forget(['checkout.address_id', 'checkout.shipping_method_id']);

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
