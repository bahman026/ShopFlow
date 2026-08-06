<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Cart\AddToCart;
use App\Actions\Cart\BuildCartSummary;
use App\Actions\Cart\GetCartLines;
use App\Actions\Cart\ResolveCartOwner;
use App\Actions\Coupon\PreviewCoupon;
use App\DTOs\CartLineDTO;
use App\Models\Cart;
use App\Models\Variety;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    /**
     * Session key holding the discount code being previewed on the cart.
     */
    private const COUPON_KEY = 'cart_coupon_code';

    public function __construct(private ResolveCartOwner $owner) {}

    public function index(Request $request, GetCartLines $getLines, BuildCartSummary $buildSummary, PreviewCoupon $previewCoupon): Response
    {
        $lines = $getLines(($this->owner)($request));

        // The cart changes under a coupon (lines added, removed, re-counted),
        // so the stored code is re-checked on every render rather than trusted.
        $code = $this->couponCode($request);
        $preview = $code === null
            ? ['coupon' => null, 'error' => null]
            : $previewCoupon($code, $request->user(), $lines);

        $coupon = $preview['coupon'];

        if ($code !== null && $coupon === null) {
            $request->session()->forget(self::COUPON_KEY);
        }

        return Inertia::render('Cart/Index', [
            'lines' => $lines->map(fn (CartLineDTO $line): array => $line->toArray())->all(),
            'summary' => $buildSummary($lines, $coupon === null ? 0 : $coupon->discount)->toArray(),
            'coupon' => $coupon?->toArray(),
            'couponError' => $preview['error'],
        ]);
    }

    /**
     * Preview a discount code against the current cart. Nothing is committed:
     * the code is only remembered in the session so the cart can show what it
     * would save (checkout applying it is Phase 4 work).
     */
    public function applyCoupon(Request $request, GetCartLines $getLines, PreviewCoupon $previewCoupon): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255'],
        ]);

        $lines = $getLines(($this->owner)($request));
        $preview = $previewCoupon($validated['code'], $request->user(), $lines);

        if ($preview['coupon'] === null) {
            $request->session()->forget(self::COUPON_KEY);

            throw ValidationException::withMessages(['code' => $preview['error']]);
        }

        $request->session()->put(self::COUPON_KEY, $preview['coupon']->code);

        return back()->with('status', trans('messages.cart.coupon.applied'));
    }

    public function removeCoupon(Request $request): RedirectResponse
    {
        $request->session()->forget(self::COUPON_KEY);

        return back()->with('status', trans('messages.cart.coupon.removed'));
    }

    /**
     * The discount code the customer is currently previewing, if any.
     */
    private function couponCode(Request $request): ?string
    {
        $code = $request->session()->get(self::COUPON_KEY);

        return is_string($code) && $code !== '' ? $code : null;
    }

    public function store(Request $request, AddToCart $add): RedirectResponse
    {
        $validated = $request->validate([
            'variety_id' => ['required', 'integer', Rule::exists('varieties', 'id')],
            'count' => ['nullable', 'integer', 'min:1'],
        ]);

        $variety = Variety::query()->findOrFail($validated['variety_id']);

        if (! $variety->has_stock || $variety->inventory < 1) {
            throw ValidationException::withMessages(['variety_id' => trans('messages.cart.unavailable')]);
        }

        $add(($this->owner)($request), $variety, (int) ($validated['count'] ?? 1));

        return back()->with('status', trans('messages.cart.added'));
    }

    public function update(Request $request, Cart $cart): RedirectResponse
    {
        $this->ensureOwner($request, $cart);

        $validated = $request->validate([
            'count' => ['required', 'integer', 'min:1'],
        ]);

        $cap = max(1, $cart->variety->inventory);
        $cart->update(['count' => min((int) $validated['count'], $cap)]);

        return back();
    }

    public function destroy(Request $request, Cart $cart): RedirectResponse
    {
        $this->ensureOwner($request, $cart);

        $cart->delete();

        return back()->with('status', trans('messages.cart.removed'));
    }

    private function ensureOwner(Request $request, Cart $cart): void
    {
        $owner = ($this->owner)($request);

        $matches = isset($owner['user_id'])
            ? $cart->user_id === $owner['user_id']
            : $cart->session_id === $owner['session_id'];

        if (! $matches) {
            abort(403);
        }
    }
}
