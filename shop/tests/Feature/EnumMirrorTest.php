<?php

declare(strict_types=1);

/**
 * The storefront mirrors the admin app's enums rather than sharing them: admin
 * owns the schema, and the shop keeps its own read-focused copies with their
 * own translation keys. The string/int values are what the two apps actually
 * agree on through the database, so they have to stay identical.
 *
 * Nothing else enforces that. Add a case to one side only and the failure is
 * silent and expensive — staff can save a value (a banner position, an order
 * status) that the storefront will never recognise, with no error anywhere.
 * These tests turn that into a build failure.
 */

/**
 * Enums that legitimately exist only on the storefront. Empty today: every
 * storefront enum mirrors an admin one. Add a name here (with a reason) if a
 * genuinely shop-only enum ever appears.
 *
 * @var array<int, string>
 */
const STOREFRONT_ONLY_ENUMS = [];

function adminEnumsPath(): string
{
    // The two apps are siblings in one repository. Production images contain
    // only their own app, but tests always run from the repo.
    return base_path('../admin/app/Enums');
}

/**
 * @return array<int, string>
 */
function storefrontEnumNames(): array
{
    return collect(glob(app_path('Enums/*.php')) ?: [])
        ->map(fn (string $path): string => basename($path, '.php'))
        ->sort()
        ->values()
        ->all();
}

/**
 * Case name => backing value, read straight off the storefront enum.
 *
 * @return array<string, string>
 */
function storefrontEnumCases(string $name): array
{
    /** @var class-string<BackedEnum> $class */
    $class = 'App\\Enums\\'.$name;

    $cases = [];

    foreach ($class::cases() as $case) {
        $cases[$case->name] = (string) $case->value;
    }

    return $cases;
}

/**
 * Case name => backing value, parsed out of the admin file. The admin classes
 * share the storefront's namespace and class names, so they cannot simply be
 * loaded — reading the source is the only way to compare them in-process.
 *
 * @return array<string, string>
 */
function adminEnumCases(string $name): array
{
    $source = (string) file_get_contents(adminEnumsPath().'/'.$name.'.php');

    preg_match_all(
        '/case\s+([A-Za-z0-9_]+)\s*=\s*(?:\'([^\']+)\'|"([^"]+)"|(-?\d+))\s*;/',
        $source,
        $matches,
        PREG_SET_ORDER,
    );

    $cases = [];

    foreach ($matches as $match) {
        // Exactly one of the three value groups matches; enum values are never
        // empty strings in this codebase, so the first non-empty one is it.
        $value = $match[2] !== '' ? $match[2] : ($match[3] !== '' ? $match[3] : ($match[4] ?? ''));
        $cases[$match[1]] = $value;
    }

    return $cases;
}

beforeEach(function (): void {
    if (! is_dir(adminEnumsPath())) {
        $this->markTestSkipped('The admin app is not alongside this one (production image).');
    }
});

it('mirrors an admin enum for every storefront enum', function (): void {
    $missing = collect(storefrontEnumNames())
        ->reject(fn (string $name): bool => in_array($name, STOREFRONT_ONLY_ENUMS, true))
        ->reject(fn (string $name): bool => is_file(adminEnumsPath().'/'.$name.'.php'))
        ->all();

    expect($missing)->toBe([], 'Storefront enums with no admin counterpart: '.implode(', ', $missing)
        .'. Either mirror them in admin/app/Enums, or list them in STOREFRONT_ONLY_ENUMS with a reason.');
});

it('keeps every mirrored enum identical to the admin app', function (): void {
    $drift = [];

    foreach (storefrontEnumNames() as $name) {
        if (in_array($name, STOREFRONT_ONLY_ENUMS, true) || ! is_file(adminEnumsPath().'/'.$name.'.php')) {
            continue;
        }

        $shop = storefrontEnumCases($name);
        $admin = adminEnumCases($name);

        // Order is irrelevant; the name => value mapping is the contract.
        ksort($shop);
        ksort($admin);

        if ($shop !== $admin) {
            $drift[] = sprintf(
                "%s\n    shop : %s\n    admin: %s",
                $name,
                json_encode($shop, JSON_UNESCAPED_UNICODE),
                json_encode($admin, JSON_UNESCAPED_UNICODE),
            );
        }
    }

    expect($drift)->toBe([], "Mirrored enums have drifted apart:\n".implode("\n", $drift));
});

it('actually reads the admin enums, rather than passing on an empty comparison', function (): void {
    // Guards the guard: a broken path or regex would make both tests above
    // pass vacuously.
    expect(storefrontEnumNames())->not->toBeEmpty()
        ->and(adminEnumCases('OrderStatusEnum'))->not->toBeEmpty()
        ->and(adminEnumCases('BannerPositionEnum'))->toHaveKey('HOME_TOP')
        ->and(adminEnumCases('SliderPositionEnum')['PRODUCT_SIDE'] ?? null)->toBe('product-side');
});
