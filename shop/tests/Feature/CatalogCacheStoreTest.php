<?php

declare(strict_types=1);

/**
 * The catalog cache only works if both apps address the *same* Redis keys, and
 * that is pure configuration — no amount of correct code makes up for it.
 *
 * This was a real, silent misconfiguration before the cache existed. Both
 * `config/cache.php` files derived the key prefix from `APP_NAME` via
 * `Str::slug`, but with different separators (`_cache_` in admin, `-cache-` in
 * the shop), and `config/database.php` did the same for the Redis prefix Redis
 * applies on top. Production was already `CACHE_STORE=redis` in both apps, so
 * the two were writing into different namespaces: every `Cache::forget()` in
 * the panel would have succeeded and cleared nothing, and the storefront would
 * have served edited-away prices until each entry expired. Nothing would have
 * logged an error.
 *
 * So the prefixes are now fixed literals, and these tests hold the two apps to
 * the same ones. They compare *files* rather than runtime config, because the
 * invariant is that the shipped configuration agrees — a machine can override
 * both apps together, and that is legitimate.
 */
function adminRoot(): string
{
    return base_path('../admin');
}

/**
 * A single `KEY=value` line out of an env file.
 */
function envValue(string $path, string $key): ?string
{
    preg_match('/^'.preg_quote($key, '/').'=(.*)$/m', (string) file_get_contents($path), $match);

    return isset($match[1]) ? trim($match[1]) : null;
}

/**
 * The literal default in `'prefix' => env('SOME_PREFIX', '<literal>')`.
 */
function configPrefixDefault(string $path, string $envKey): ?string
{
    preg_match(
        "/'prefix'\s*=>\s*env\(\s*'".preg_quote($envKey, '/')."'\s*,\s*'([^']*)'\s*\)/",
        (string) file_get_contents($path),
        $match,
    );

    return $match[1] ?? null;
}

beforeEach(function (): void {
    if (! is_dir(adminRoot())) {
        $this->markTestSkipped('The admin app is not alongside this one (production image).');
    }
});

it('falls back to the same cache key prefix in both apps', function (): void {
    $shop = configPrefixDefault(config_path('cache.php'), 'CACHE_PREFIX');
    $admin = configPrefixDefault(adminRoot().'/config/cache.php', 'CACHE_PREFIX');

    expect($shop)->not->toBeNull('shop/config/cache.php no longer pins a literal CACHE_PREFIX default.')
        ->and($admin)->not->toBeNull('admin/config/cache.php no longer pins a literal CACHE_PREFIX default.')
        ->and($shop)->toBe($admin, 'The two apps would address different cache keys, so cross-app invalidation would reach nothing.');
});

it('falls back to the same redis key prefix in both apps', function (): void {
    // Redis applies this on top of the cache prefix, so a mismatch here breaks
    // invalidation just as completely as a mismatched cache prefix.
    $shop = configPrefixDefault(config_path('database.php'), 'REDIS_PREFIX');
    $admin = configPrefixDefault(adminRoot().'/config/database.php', 'REDIS_PREFIX');

    expect($shop)->not->toBeNull('shop/config/database.php no longer pins a literal REDIS_PREFIX default.')
        ->and($admin)->not->toBeNull('admin/config/database.php no longer pins a literal REDIS_PREFIX default.')
        ->and($shop)->toBe($admin, 'The two apps would address different Redis keys.');
});

it('ships both apps on one shared cache store', function (): void {
    $shopStore = envValue(base_path('.env.example'), 'CACHE_STORE');
    $adminStore = envValue(adminRoot().'/.env.example', 'CACHE_STORE');

    expect($shopStore)->toBe($adminStore, 'Both apps must use the same cache store or neither can invalidate the other.')
        // `file` and `database` are per-app stores: the panel would clear rows
        // the storefront never reads. Only a shared server works.
        ->and($shopStore)->toBe('redis');
});

it('pins matching cache and redis prefixes in both apps env examples', function (): void {
    foreach (['CACHE_PREFIX', 'REDIS_PREFIX'] as $key) {
        expect(envValue(base_path('.env.example'), $key))
            ->toBe(envValue(adminRoot().'/.env.example', $key), $key.' differs between the two .env.example files.')
            ->not->toBeNull($key.' is not set in .env.example, so the two apps fall back to their own defaults.')
            ->not->toBe('', $key.' is empty, which Laravel reads as an empty string rather than the config default.');
    }
});

it('ships production on the same shared store and prefixes', function (): void {
    // Production is where this matters most, and where it was already wrong:
    // both apps were on redis with no pinned prefix.
    $shopProd = base_path('.env.production.example');
    $adminProd = adminRoot().'/.env.production.example';

    foreach (['CACHE_STORE', 'CACHE_PREFIX', 'REDIS_PREFIX'] as $key) {
        expect(envValue($shopProd, $key))
            ->toBe(envValue($adminProd, $key), $key.' differs between the two production env examples.')
            ->not->toBeNull($key.' is missing from a production env example.')
            ->not->toBe('', $key.' is empty in a production env example.');
    }
});

it('actually reads the admin config, rather than passing on nulls', function (): void {
    // Guards the guards: a wrong path or a broken regex would make every
    // comparison above compare null to null and pass.
    expect(configPrefixDefault(adminRoot().'/config/cache.php', 'CACHE_PREFIX'))->not->toBeNull()
        ->and(envValue(adminRoot().'/.env.example', 'CACHE_STORE'))->toBe('redis')
        ->and(envValue(base_path('.env.example'), 'CACHE_STORE'))->toBe('redis');
});
