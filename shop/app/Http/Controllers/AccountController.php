<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Account\GetUserOrders;
use App\Actions\Account\GetUserWishlist;
use App\Actions\Checkout\BuildOrderDTO;
use App\Actions\Checkout\RetryOrderPayment;
use App\DTOs\UserDTO;
use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class AccountController extends Controller
{
    public function dashboard(Request $request): Response
    {
        return Inertia::render('Account/Dashboard', [
            'user' => $this->userDto($request)->toArray(),
        ]);
    }

    public function profile(Request $request): Response
    {
        $user = $this->user($request);
        $data = $this->userDto($request)->toArray();

        // Hide the synthetic OTP placeholder so the field shows empty.
        if ($user->hasPlaceholderEmail()) {
            $data['email'] = null;
        }

        return Inertia::render('Account/Profile', ['user' => $data]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $this->user($request);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->first_name = $validated['first_name'];
        $user->last_name = $validated['last_name'];

        $email = $validated['email'] ?? null;

        if ($email !== null && $email !== '' && $email !== $user->email) {
            $user->email = $email;
            $user->email_verified_at = null;
        }

        $user->save();

        return back()->with('status', 'اطلاعات حساب با موفقیت ذخیره شد.');
    }

    public function orders(Request $request, GetUserOrders $getUserOrders): Response
    {
        return Inertia::render('Account/Orders/Index', [
            'orders' => $getUserOrders($this->user($request)),
        ]);
    }

    public function showOrder(Request $request, Order $order, BuildOrderDTO $buildOrderDTO): Response
    {
        if ($order->user_id !== $this->user($request)->id) {
            abort(403);
        }

        return Inertia::render('Account/Orders/Show', [
            'order' => $buildOrderDTO($order)->toArray(),
        ]);
    }

    public function receipt(Request $request, Order $order, BuildOrderDTO $buildOrderDTO): Response
    {
        if ($order->user_id !== $this->user($request)->id) {
            abort(403);
        }

        return Inertia::render('Account/Orders/Receipt', [
            'order' => $buildOrderDTO($order)->toArray(),
        ]);
    }

    public function retryOrder(Request $request, Order $order, RetryOrderPayment $retryPayment): RedirectResponse|SymfonyResponse
    {
        $user = $this->user($request);

        if ($order->user_id !== $user->id) {
            abort(403);
        }

        if (! $order->isRetryable()) {
            abort(403);
        }

        $url = $retryPayment($order, $user, route('checkout.callback'), (string) $request->ip());

        if ($url === null) {
            return redirect()->route('account.orders.show', $order)
                ->with('status', 'متأسفانه موجودی برخی از کالاهای این سفارش دیگر کافی نیست.');
        }

        return Inertia::location($url);
    }

    public function returns(Request $request, GetUserOrders $getUserOrders): Response
    {
        return Inertia::render('Account/Orders/Index', [
            'orders' => $getUserOrders($this->user($request), OrderStatusEnum::RETURNED),
            'title' => 'مرجوعی‌های من',
            'emptyTitle' => 'هنوز مرجوعی‌ای ثبت نشده است',
            'emptyDescription' => 'سفارش‌های مرجوع‌شده شما اینجا نمایش داده می‌شوند.',
            'baseUrl' => '/account/returns',
        ]);
    }

    public function wishlist(Request $request, GetUserWishlist $getUserWishlist): Response
    {
        return Inertia::render('Account/Wishlist/Index', [
            'products' => $getUserWishlist($this->user($request)),
        ]);
    }

    public function reviews(): Response
    {
        return $this->comingSoon('نظرات ثبت‌شده');
    }

    private function comingSoon(string $title): Response
    {
        return Inertia::render('Account/ComingSoon', ['title' => $title]);
    }

    private function user(Request $request): User
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        return $user;
    }

    private function userDto(Request $request): UserDTO
    {
        $user = $this->user($request);

        return new UserDTO(
            id: $user->id,
            displayName: $user->displayName(),
            mobile: $user->mobile,
            firstName: $user->first_name,
            lastName: $user->last_name,
            email: $user->email,
        );
    }
}
