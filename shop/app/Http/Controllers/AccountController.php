<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTOs\UserDTO;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

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

    public function orders(): Response
    {
        return $this->comingSoon('سفارش‌های من');
    }

    public function returns(): Response
    {
        return $this->comingSoon('مرجوعی‌های من');
    }

    public function wishlist(): Response
    {
        return $this->comingSoon('علاقه‌مندی‌های من');
    }

    public function addresses(): Response
    {
        return $this->comingSoon('نشانی‌ها');
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
