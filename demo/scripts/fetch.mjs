// Fetches images for the demo dataset from the Pexels API (commercial/
// lifestyle stock photography — a much better fit than Wikimedia Commons,
// which is dominated by historical/archive/documentary content and kept
// returning irrelevant matches for plain product-style queries), resizes to
// 1200px longest side, converts to WebP, and writes straight into
// admin/storage/app/public/demo/<dest>.
//
// One Pexels search call per DISTINCT QUERY (cached), not per manifest entry —
// a product's 2 images both come from one search's top results (rank 0/1), so
// ~93 API calls cover the 143 manifest entries. Safe to re-run: skips any
// entry whose output file already exists. Retries 429/5xx with backoff.
// Records photographer/credit per used image in demo/data/image_credits.json
// (Pexels doesn't require attribution, but it's kept as a paper trail).
//
// Usage: PEXELS_API_KEY=... npm run fetch   (or put the key in demo/.env,
// gitignored — see ../README.md)

import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import sharp from 'sharp';

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..', '..');
const DATA = path.join(ROOT, 'demo', 'data');
const RAW = path.join(ROOT, 'demo', 'raw');
const OUT = path.join(ROOT, 'admin', 'storage', 'app', 'public', 'demo');
const REQUEST_DELAY_MS = 600;
const TIMEOUT_MS = 20000;

function loadEnv(file) {
    const out = {};
    if (!fs.existsSync(file)) return out;
    for (const line of fs.readFileSync(file, 'utf-8').split('\n')) {
        const m = line.match(/^([A-Z0-9_]+)=(.*)$/);
        if (m) out[m[1]] = m[2].trim();
    }
    return out;
}

const env = { ...loadEnv(path.join(ROOT, 'demo', '.env')), ...process.env };
const API_KEY = env.PEXELS_API_KEY;
if (!API_KEY) {
    console.error('Missing PEXELS_API_KEY (expected in demo/.env or the environment).');
    process.exit(1);
}

function sleep(ms) {
    return new Promise((r) => setTimeout(r, ms));
}

async function pexelsSearch(query, attempt = 1) {
    const url = `https://api.pexels.com/v1/search?query=${encodeURIComponent(query)}&per_page=5&orientation=landscape`;
    const res = await fetch(url, {
        headers: { Authorization: API_KEY },
        signal: AbortSignal.timeout(TIMEOUT_MS),
    });
    if (res.status === 429 || res.status >= 500) {
        if (attempt > 5) throw new Error(`giving up after ${attempt} attempts: ${res.status} ${url}`);
        const backoff = 2000 * 2 ** attempt;
        console.log(`  [retry ${attempt}] HTTP ${res.status}, waiting ${backoff}ms`);
        await sleep(backoff);
        return pexelsSearch(query, attempt + 1);
    }
    if (!res.ok) throw new Error(`HTTP ${res.status} for ${url}: ${await res.text()}`);
    return res.json();
}

async function fetchBinary(url, attempt = 1) {
    const res = await fetch(url, { signal: AbortSignal.timeout(TIMEOUT_MS) });
    if (res.status === 429 || res.status >= 500) {
        if (attempt > 5) throw new Error(`giving up after ${attempt} attempts: ${res.status} ${url}`);
        const backoff = 2000 * 2 ** attempt;
        console.log(`  [retry ${attempt}] HTTP ${res.status}, waiting ${backoff}ms`);
        await sleep(backoff);
        return fetchBinary(url, attempt + 1);
    }
    if (!res.ok) throw new Error(`HTTP ${res.status} for ${url}`);
    return Buffer.from(await res.arrayBuffer());
}

const queryCache = new Map();

/** Search once per distinct query string, cached both in-memory and on disk. */
async function searchQuery(query) {
    if (queryCache.has(query)) return queryCache.get(query);

    const safeName = query.replace(/[^a-z0-9]+/gi, '_').toLowerCase();
    const cacheFile = path.join(RAW, 'search', `${safeName}.json`);

    let json;
    if (fs.existsSync(cacheFile)) {
        json = JSON.parse(fs.readFileSync(cacheFile, 'utf-8'));
    } else {
        json = await pexelsSearch(query);
        fs.writeFileSync(cacheFile, JSON.stringify(json, null, 2), 'utf-8');
        await sleep(REQUEST_DELAY_MS);
    }
    queryCache.set(query, json);
    return json;
}

async function main() {
    const manifest = JSON.parse(fs.readFileSync(path.join(DATA, 'image_manifest.json'), 'utf-8'));
    fs.mkdirSync(path.join(RAW, 'search'), { recursive: true });
    fs.mkdirSync(path.join(RAW, 'images'), { recursive: true });
    fs.mkdirSync(OUT, { recursive: true });

    const credits = fs.existsSync(path.join(DATA, 'image_credits.json'))
        ? JSON.parse(fs.readFileSync(path.join(DATA, 'image_credits.json'), 'utf-8'))
        : {};

    let done = 0, skipped = 0, failed = 0;

    for (const entry of manifest) {
        const outPath = path.join(OUT, entry.dest);
        fs.mkdirSync(path.dirname(outPath), { recursive: true });

        if (fs.existsSync(outPath)) {
            skipped++;
            continue;
        }

        const rank = entry.rank ?? 0;

        try {
            const json = await searchQuery(entry.query);
            const photo = json?.photos?.[rank];
            if (!photo) {
                console.log(`NO MATCH  ${entry.dest}  (query: "${entry.query}", rank ${rank}, got ${json?.photos?.length ?? 0} results)`);
                failed++;
                continue;
            }

            const safeName = entry.dest.replace(/[\\/]/g, '__').replace(/\.webp$/, '');
            const rawImgPath = path.join(RAW, 'images', `${safeName}.jpg`);

            let bytes;
            if (fs.existsSync(rawImgPath)) {
                bytes = fs.readFileSync(rawImgPath);
            } else {
                bytes = await fetchBinary(photo.src.large2x || photo.src.large);
                fs.writeFileSync(rawImgPath, bytes);
                await sleep(REQUEST_DELAY_MS);
            }

            await sharp(bytes)
                .rotate()
                .resize({ width: 1200, height: 1200, fit: 'inside', withoutEnlargement: true })
                .webp({ quality: 82 })
                .toFile(outPath);

            credits[entry.dest] = {
                id: photo.id,
                photographer: photo.photographer,
                photographer_url: photo.photographer_url,
                source_page: photo.url,
                license: 'Pexels License (free for commercial use, no attribution required)',
            };
            done++;
            console.log(`OK  ${entry.dest}  <-  photo #${photo.id} by ${photo.photographer}`);
        } catch (err) {
            console.log(`FAIL  ${entry.dest}: ${err.message}`);
            failed++;
        }
    }

    fs.writeFileSync(path.join(DATA, 'image_credits.json'), JSON.stringify(credits, null, 2), 'utf-8');
    console.log(`\nDone. fetched=${done} skipped(existing)=${skipped} failed=${failed} total=${manifest.length} (${queryCache.size} distinct queries)`);
}

main().catch((e) => {
    console.error(e);
    process.exit(1);
});
