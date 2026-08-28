# Demo catalog dataset

Realistic-looking demo content for local dev and staging — categories,
attributes, brands, ~50 products with variants, homepage banners/sliders,
tags, and a header menu. Everything here is original/invented content
(no scraping), seeded by `admin/database/seeders/DemoSeeder.php`.

## What's tracked in git, and what isn't

| Path | Tracked? | Why |
|---|---|---|
| `demo/data/*.json` | Yes | Hand-authored content. Small, no secrets. |
| `demo/scripts/` | Yes | The tool that fetches product/category photos. Code, no secrets. |
| `demo/scripts/node_modules/` | No | Reinstalled with `npm install`. |
| `demo/.env` | **No** | Holds your Pexels API key. |
| `demo/raw/` | No | Fetch cache (search results + original downloads). Regenerated on demand. |
| `admin/storage/app/public/demo/` | No | The actual processed images. Ignored by Laravel's own default `storage/app/public/.gitignore` — this is a *separate* rule from anything in this repo's root `.gitignore`. |

In short: the **definition** of the demo (what products/categories/banners
exist, with what text) is versioned. The **photos** are not — they're
fetched from Pexels on demand, once, per machine.

## Regenerating the images

Needed once per machine (local dev, staging, a fresh clone) before running
`DemoSeeder`, unless `admin/storage/app/public/demo/` already has the files
(e.g. copied over separately).

1. Get a free Pexels API key: https://www.pexels.com/api/ (instant, no
   approval wait).
2. Put it in `demo/.env` (create the file — it's gitignored):
   ```
   PEXELS_API_KEY=your-key-here
   ```
3. Install the fetch script's one dependency (`sharp`, for resizing/WebP):
   ```bash
   cd demo/scripts
   npm install
   ```
4. Run the fetch (takes a few minutes — ~93 distinct searches, 143 output
   files, resized to 1200px longest side and converted to WebP):
   ```bash
   npm run fetch
   ```
   Safe to re-run: it skips any output file that already exists, so an
   interrupted run just picks up where it left off. It also caches every
   Pexels search result and downloaded original under `demo/raw/`, so
   re-running after clearing only `admin/storage/app/public/demo/` doesn't
   re-hit the API at all.

If you ever add/change a product, category, banner, or tag in
`demo/data/*.json` and it needs a new photo, regenerate the manifest first:
```bash
npm run manifest   # rebuilds demo/data/image_manifest.json from the JSON above
npm run fetch       # fetches only the new/changed entries
```

## Then seed

From `admin/`, with the images in place:
```bash
php artisan migrate:fresh --seed
```
`DemoSeeder` only runs in `local`/`staging` environments (see
`DatabaseSeeder`) and is safe to run more than once.

## Attribution

`demo/data/image_credits.json` (tracked) records the Pexels photo id,
photographer, and source URL for every image actually used. The Pexels
License doesn't require attribution, but it's kept as a paper trail.
