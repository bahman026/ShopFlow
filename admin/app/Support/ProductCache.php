<?php

declare(strict_types=1);

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Catalog cache for products and their varieties, shared by both apps.
 *
 * This file is **mirrored byte-for-byte** between `admin/` and `shop/` (like the
 * enums, and enforced by `shop/tests/Feature/ProductCacheMirrorTest.php`). The
 * storefront writes these entries; the admin panel deletes them when staff edit
 * a product. Two copies of the same key builder are what make that work, so a
 * change to one side alone silently strands the other side's entries.
 *
 * The two apps must also agree on the *store*: `CACHE_STORE=redis` plus the
 * pinned `CACHE_PREFIX`/`REDIS_PREFIX` in `config/cache.php`/`config/database.php`.
 * On separate stores every method here still succeeds and invalidation reaches
 * nothing at all.
 *
 * ## Why keys carry a generation token
 *
 * A product's own page can be forgotten precisely — we know its slug. Lists
 * cannot: a category page's payload varies by six filters, a sort and a page
 * number, so the key space is unbounded and there is nothing to enumerate.
 * Every key therefore embeds a generation token, and invalidating a whole class
 * of entries is one write that changes the token. Older entries become
 * unreachable and expire on their own TTL.
 *
 * Detail and list entries carry *separate* generations on purpose. Selling the
 * last unit of a product changes `varieties.inventory`, which the product page
 * shows and no product card does — so a purchase forgets one product page and
 * leaves every category listing warm.
 */
class ProductCache
{
    /**
     * Product detail payload — `CACHE.md` key 5. Kept longer than the lists
     * because it is invalidated precisely, by slug.
     */
    public const int DETAIL_TTL = 1800;

    /**
     * Paginated product-card lists — `CACHE.md` key 6.
     */
    public const int LIST_TTL = 900;

    private const string DETAIL_GENERATION_KEY = 'products.generation.detail';

    private const string LIST_GENERATION_KEY = 'products.generation.list';

    /**
     * Generation tokens resolved during this request, so a page that reads a
     * product and a list does not fetch the same token twice. Cleared whenever
     * this process bumps a generation, so a write followed by a read in the
     * same request cannot address the stale namespace.
     *
     * @var array<string, string>
     */
    private static array $generations = [];

    /**
     * Remember one product's page payload, keyed by slug.
     *
     * @template TValue
     *
     * @param  Closure(): TValue  $build
     * @return TValue
     */
    public static function rememberDetail(string $slug, Closure $build): mixed
    {
        /** @var TValue $payload */
        $payload = Cache::remember(self::detailKey($slug), self::DETAIL_TTL, $build);

        return $payload;
    }

    /**
     * Remember a list of product cards. `$signature` is every input the payload
     * depends on (category ids, filters, sort, page); it is fingerprinted, so
     * callers can pass it in whatever shape reads naturally.
     *
     * @template TValue
     *
     * @param  array<string, mixed>  $signature
     * @param  Closure(): TValue  $build
     * @return TValue
     */
    public static function rememberList(string $scope, array $signature, Closure $build): mixed
    {
        /** @var TValue $payload */
        $payload = Cache::remember(self::listKey($scope, $signature), self::LIST_TTL, $build);

        return $payload;
    }

    public static function detailKey(string $slug): string
    {
        return 'products.' . self::generation(self::DETAIL_GENERATION_KEY) . '.slug.' . $slug;
    }

    /**
     * @param  array<string, mixed>  $signature
     */
    public static function listKey(string $scope, array $signature): string
    {
        return 'products.' . self::generation(self::LIST_GENERATION_KEY)
            . '.list.' . $scope . '.' . self::fingerprint($signature);
    }

    /**
     * Drop the cached pages for one product.
     *
     * Takes slugs rather than an id because that is what the key is built from.
     * Pass the original slug as well as the current one when a rename is
     * possible — otherwise the old URL keeps serving the pre-edit page for the
     * rest of its TTL.
     */
    public static function forgetProduct(?string ...$slugs): void
    {
        foreach (array_unique(array_filter($slugs)) as $slug) {
            Cache::forget(self::detailKey($slug));
        }
    }

    /**
     * Invalidate every cached product list, in one write.
     *
     * Call this for a change a product *card* can show (price, stock flag,
     * status, category, brand, heading, image) — not for a change only the
     * product page shows.
     */
    public static function flushLists(): void
    {
        self::bump(self::LIST_GENERATION_KEY);
    }

    /**
     * Invalidate every cached product page and list.
     *
     * For writes that bypass model events, so no observer sees them: a seeder
     * `truncate()`, a mass `update()` on the query builder, a manual SQL fix.
     */
    public static function flushAll(): void
    {
        self::bump(self::DETAIL_GENERATION_KEY);
        self::bump(self::LIST_GENERATION_KEY);
    }

    /**
     * A random token, not a counter: `increment()` behaves differently across
     * drivers on a missing key, and two racing bumps writing the same counter
     * value would leave stale entries reachable. Any change of value is enough.
     */
    private static function bump(string $generationKey): void
    {
        unset(self::$generations[$generationKey]);

        Cache::forever($generationKey, Str::random(10));
    }

    private static function generation(string $generationKey): string
    {
        if (isset(self::$generations[$generationKey])) {
            return self::$generations[$generationKey];
        }

        /** @var string $token */
        $token = Cache::rememberForever($generationKey, fn (): string => Str::random(10));

        return self::$generations[$generationKey] = $token;
    }

    /**
     * @param  array<string, mixed>  $signature
     */
    private static function fingerprint(array $signature): string
    {
        return md5((string) json_encode(self::normalise($signature)));
    }

    /**
     * Sort recursively so equivalent filters fingerprint the same, whatever
     * order they arrived in: `?brands=a,b` and `?brands=b,a` are one page.
     */
    private static function normalise(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        $value = array_map(static fn (mixed $item): mixed => self::normalise($item), $value);

        if (array_is_list($value)) {
            sort($value);

            return $value;
        }

        ksort($value);

        return $value;
    }
}
