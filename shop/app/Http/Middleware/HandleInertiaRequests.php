<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\CategoryStatusEnum;
use App\Models\Category;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $settings = $this->autoloadedSettings();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $this->authUser($request),
            ],
            'flash' => [
                'status' => $request->session()->get('status'),
                'authStep' => $request->session()->get('authStep'),
                'authMobile' => $request->session()->get('authMobile'),
                'authResendIn' => $request->session()->get('authResendIn'),
                'authOtpDev' => $request->session()->get('authOtpDev'),
            ],
            'seo' => [
                'siteName' => (string) config('app.name'),
                'url' => $this->canonicalUrl($request),
                'origin' => rtrim((string) config('app.url'), '/'),
                'locale' => 'fa_IR',
            ],
            'nav' => [
                'categories' => $this->navCategories(),
            ],
            'footer' => [
                'about' => $this->value($settings, 'footer_about'),
                'columns' => [
                    ['title' => 'فروشگاه', 'links' => $this->json($settings, 'footer_links_shop')],
                    ['title' => 'پشتیبانی', 'links' => $this->json($settings, 'footer_links_support')],
                ],
                'contact' => [
                    'title' => 'ارتباط با ما',
                    'phone' => $this->value($settings, 'site_phone'),
                    'email' => $this->value($settings, 'site_email'),
                    'hours' => $this->json($settings, 'site_working_hours'),
                    'address' => $this->value($settings, 'site_address'),
                ],
                'socials' => $this->json($settings, 'footer_socials'),
                'copyright' => $this->value($settings, 'footer_copyright'),
            ],
        ];
    }

    /**
     * The authenticated user as a small payload for the header/account UI.
     *
     * @return array<string, mixed>|null
     */
    private function authUser(Request $request): ?array
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return null;
        }

        return [
            'id' => $user->id,
            'name' => $user->displayName(),
            'mobile' => $user->mobile,
        ];
    }

    /**
     * Top-level active categories (with their active children) for the header
     * navigation. Shared globally so every page's header has the menu.
     *
     * Guarded with rescue() so the storefront still renders if the shared
     * categories table is unavailable.
     *
     * @return array<int, array<string, mixed>>
     */
    private function navCategories(): array
    {
        return rescue(function (): array {
            return Category::query()
                ->active()
                ->whereNull('parent_id')
                ->with(['children' => fn (Relation $query) => $query->where('status', CategoryStatusEnum::ACTIVE->value)->orderBy('heading')])
                ->orderBy('heading')
                ->get()
                ->map(fn (Category $category): array => [
                    'heading' => $category->heading,
                    'url' => '/categories/'.$category->slug,
                    'children' => $category->children
                        ->map(fn (Category $child): array => [
                            'heading' => $child->heading,
                            'url' => '/categories/'.$child->slug,
                        ])
                        ->all(),
                ])
                ->all();
        }, [], false);
    }

    /**
     * Build the canonical/og URL from the configured public origin (APP_URL)
     * rather than the raw request, so it stays correct behind TLS-terminating
     * proxies regardless of the internal scheme/host.
     */
    private function canonicalUrl(Request $request): string
    {
        $base = rtrim((string) config('app.url'), '/');
        $path = $request->path();

        return $path === '/' ? $base : $base.'/'.$path;
    }

    /**
     * Load the globally autoloaded settings as a key => content map.
     *
     * Guarded with rescue() so the storefront still renders in environments
     * where the admin-owned settings table is absent (e.g. the test database).
     *
     * @return Collection<array-key, mixed>
     */
    private function autoloadedSettings(): Collection
    {
        /** @var Collection<array-key, mixed> $settings */
        $settings = rescue(
            fn (): Collection => Setting::query()->autoloaded()->pluck('content', 'key'),
            collect(),
            false,
        );

        return $settings;
    }

    /**
     * @param  Collection<array-key, mixed>  $settings
     */
    private function value(Collection $settings, string $key): ?string
    {
        $value = $settings->get($key);

        return is_string($value) ? $value : null;
    }

    /**
     * Decode a JSON setting value into an array.
     *
     * @param  Collection<array-key, mixed>  $settings
     * @return array<int, mixed>
     */
    private function json(Collection $settings, string $key): array
    {
        $value = $this->value($settings, $key);

        if ($value === null || $value === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }
}
