// Builds the image-fetch manifest from demo/data/*.json.
// One entry per image the demo needs: {dest, query, kind}.
// dest is relative to admin/storage/app/public/demo/.

import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..', '..');
const DATA = path.join(ROOT, 'demo', 'data');

function readJson(name) {
    return JSON.parse(fs.readFileSync(path.join(DATA, name), 'utf-8'));
}

const categories = readJson('categories.json');
const products = readJson('products.json');
const homepage = readJson('homepage.json');
const tags = readJson('tags.json');

const manifest = [];

// Categories: 1 image per parent + 1 per child.
for (const parent of categories) {
    manifest.push({ dest: `categories/${parent.slug}.webp`, query: parent.image_query, kind: 'square' });
    for (const child of parent.children) {
        manifest.push({ dest: `categories/${child.slug}.webp`, query: child.image_query, kind: 'square' });
    }
}

// Products: 2 images each (product-1.webp, product-2.webp), same query
// (different search result rank per image so they're not identical).
for (const p of products) {
    manifest.push({ dest: `products/${p.slug}-1.webp`, query: p.image_query, kind: 'square', rank: 0 });
    manifest.push({ dest: `products/${p.slug}-2.webp`, query: p.image_query, kind: 'square', rank: 1 });
}

// Banners: one image per banner, keyed by position + index within that position.
const bannerCounters = {};
for (const b of homepage.banners) {
    const i = (bannerCounters[b.position] = (bannerCounters[b.position] || 0) + 1);
    manifest.push({ dest: `banners/${b.position}-${i}.webp`, query: b.image_query, kind: 'wide' });
}

// Sliders: one image per slide, keyed by slider position + slide order.
for (const s of homepage.sliders) {
    for (const slide of s.slides) {
        manifest.push({ dest: `sliders/${s.position}-${slide.order}.webp`, query: slide.image_query, kind: 'wide' });
    }
}

// Tags: one image per tag (only ones shown on home actually get used, but
// fetch for all six — cheap, and lets that decision change later for free).
for (const t of tags) {
    manifest.push({ dest: `tags/${t.slug}.webp`, query: t.image_query || t.name, kind: 'square' });
}

fs.writeFileSync(path.join(DATA, 'image_manifest.json'), JSON.stringify(manifest, null, 2), 'utf-8');
console.log(`Wrote ${manifest.length} manifest entries to demo/data/image_manifest.json`);
