<?php

declare(strict_types=1);

/**
 * The catalog cache spans both apps: the storefront writes the entries, the
 * admin panel deletes them when staff edit a product. That works only while the
 * two sides build the *same keys* and agree on which column changes matter, so
 * `App\Support\ProductCache` and the four observers exist as one file copied
 * into each app — the same arrangement as the mirrored enums, and the same
 * failure mode if they drift: no error anywhere, just a storefront serving
 * prices that were edited away, until each entry's TTL runs out.
 *
 * These tests compare the copies as *code*: PHP token streams with whitespace
 * and comments removed. A byte comparison would be wrong, because the two apps
 * format differently on purpose — admin's `pint.json` pins
 * `concat_space: one`, the shop uses Pint's default — so the same source is
 * legitimately spaced differently in each app.
 */

/**
 * Files that must stay identical, relative to each app's `app/` directory.
 *
 * @var array<int, string>
 */
const MIRRORED_CACHE_FILES = [
    'Support/ProductCache.php',
    'Observers/Concerns/ForgetsProductCache.php',
    'Observers/Concerns/FlushesCatalogCache.php',
    'Observers/ProductObserver.php',
    'Observers/VarietyObserver.php',
    'Observers/ReviewObserver.php',
    'Observers/ImageObserver.php',
    'Observers/CategoryObserver.php',
    'Observers/BrandObserver.php',
    'Observers/AttributeObserver.php',
    'Observers/AttributeGroupObserver.php',
];

function adminAppPath(): string
{
    // The two apps are siblings in one repository. Production images contain
    // only their own app, but tests always run from the repo.
    return base_path('../admin/app');
}

/**
 * The file as significant PHP tokens: no whitespace, no comments. Two files
 * with the same stream are the same program.
 *
 * @return array<int, string>
 */
function significantTokens(string $path): array
{
    $ignored = [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT];

    $tokens = [];

    foreach (PhpToken::tokenize((string) file_get_contents($path)) as $token) {
        if (in_array($token->id, $ignored, true)) {
            continue;
        }

        $tokens[] = $token->text;
    }

    return $tokens;
}

beforeEach(function (): void {
    if (! is_dir(adminAppPath())) {
        $this->markTestSkipped('The admin app is not alongside this one (production image).');
    }
});

it('keeps every mirrored catalog-cache file identical to the admin app', function (): void {
    $drift = [];

    foreach (MIRRORED_CACHE_FILES as $file) {
        $shopPath = app_path($file);
        $adminPath = adminAppPath().'/'.$file;

        if (! is_file($adminPath)) {
            $drift[] = $file.' — missing in admin/app';

            continue;
        }

        if (significantTokens($shopPath) !== significantTokens($adminPath)) {
            $drift[] = $file.' — the two copies differ beyond formatting and comments';
        }
    }

    expect($drift)->toBe([], "Mirrored catalog-cache files have drifted apart:\n  ".implode("\n  ", $drift)
        ."\nCopy the intended version into both apps, then run each app's Pint.");
});

it('has the mirrored files present in this app in the first place', function (): void {
    $missing = collect(MIRRORED_CACHE_FILES)
        ->reject(fn (string $file): bool => is_file(app_path($file)))
        ->all();

    expect($missing)->toBe([], 'Missing from shop/app: '.implode(', ', $missing));
});

it('actually compares the files, rather than passing on an empty comparison', function (): void {
    // Guards the guard: a broken path or a token filter that dropped
    // everything would make the test above pass vacuously.
    $tokens = significantTokens(app_path('Support/ProductCache.php'));

    expect($tokens)->not->toBeEmpty()
        ->and($tokens)->toContain('ProductCache')
        ->and($tokens)->toContain('flushLists')
        // The literal that the two apps' keys are actually built from.
        ->and($tokens)->toContain("'products.generation.list'");
});

it('proves formatting alone does not fail the comparison', function (): void {
    // The reason this suite compares tokens instead of bytes: admin's Pint
    // config spaces concatenation, the shop's does not.
    $spaced = __DIR__.'/../../storage/framework/testing/mirror-spaced.php';
    $tight = __DIR__.'/../../storage/framework/testing/mirror-tight.php';

    file_put_contents($spaced, "<?php\n\n// a comment\n\$a = 'x' . 'y';\n");
    file_put_contents($tight, "<?php\n\$a = 'x'.'y';\n");

    expect(significantTokens($spaced))->toBe(significantTokens($tight));

    unlink($spaced);
    unlink($tight);
});
