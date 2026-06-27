<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Cart\AddToCart;
use App\Actions\Cart\BuildCartSummary;
use App\Actions\Cart\GetCartLines;
use App\Actions\Cart\ResolveCartOwner;
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
    public function __construct(private ResolveCartOwner $owner) {}

    public function index(Request $request, GetCartLines $getLines, BuildCartSummary $buildSummary): Response
    {
        $lines = $getLines(($this->owner)($request));

        return Inertia::render('Cart/Index', [
            'lines' => $lines->map(fn (CartLineDTO $line): array => $line->toArray())->all(),
            'summary' => $buildSummary($lines)->toArray(),
        ]);
    }

    public function store(Request $request, AddToCart $add): RedirectResponse
    {
        $validated = $request->validate([
            'variety_id' => ['required', 'integer', Rule::exists('varieties', 'id')],
            'count' => ['nullable', 'integer', 'min:1'],
        ]);

        $variety = Variety::query()->findOrFail($validated['variety_id']);

        if (! $variety->has_stock || $variety->inventory < 1) {
            throw ValidationException::withMessages(['variety_id' => 'این کالا موجود نیست.']);
        }

        $add(($this->owner)($request), $variety, (int) ($validated['count'] ?? 1));

        return back()->with('status', 'کالا به سبد خرید اضافه شد.');
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

        return back()->with('status', 'کالا از سبد خرید حذف شد.');
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
