# ShopFlow

ShopFlow is an **open-source, single-vendor e-commerce platform** (MIT licensed) for building a Persian, right-to-left online store. It is designed for one business selling its own products (no marketplace/seller system), with a strong focus on SEO and a server-rendered storefront so pages are fast and indexable.

It is built as a monorepo of two Laravel apps that share one PostgreSQL database:

- **`admin/`** — the management panel (Laravel 13 + Filament 5, PHP 8.5). It **owns the database schema** (all migrations live here) and is where the team manages catalog, orders, content, and settings.
- **`shop/`** — the customer-facing storefront (Laravel 13 + Inertia + Vue 3 with SSR, PHP 8.5). It mostly **reads** catalog/pricing data and writes carts, orders, addresses, and payments. The UI is Persian, RTL-first.

The two apps never duplicate tables: `admin` migrates the shared schema, and `shop` adds read-focused Eloquent models that map to the same tables.

## What ShopFlow provides

The platform models a full online-store domain:

- **Catalog** — hierarchical categories, products with purchasable varieties (e.g. size/color), brands, attributes and attribute groups, and images.
- **Discovery** — faceted category filtering (brand, attribute, price, availability), sorting, pagination, and attribute-based SEO landing pages (tags).
- **Pricing & promotions** — per-variety pricing, sale prices, discounts and coupons.
- **Cart & checkout** — carts, orders with line snapshots, per-city shipping methods, and inventory that is only decremented on a successful payment.
- **Payments** — manual receipts (card-to-card / Paya) and online gateways (Mellat / Parsian / Zarinpal) via transactions.
- **Customers** — accounts, addresses kept as immutable history, wishlists, product reviews, points, and newsletters.
- **SEO** — SSR HTML, unique titles/meta, canonical URLs, Open Graph, JSON-LD structured data, sitemap, and redirects.

The admin panel covers the full schema today. The storefront is built feature by feature against `shop/docs/STOREFRONT_IMPLEMENTATION.md`; see that roadmap for current status.

## Repository structure

```
ShopFlow/
├── compose.yaml        # Root entry point: brings up all six containers
├── admin/              # Filament admin panel (owns the DB schema)
├── shop/               # Inertia + Vue storefront (SSR)
├── infrastructure/
│   └── docker/         # Shared Postgres + Redis (docker compose)
├── .github/workflows/  # CI (deploy-application.yml)
└── README.md
```

## Tech stack

- PHP 8.5, Laravel 13
- Admin: Filament 5
- Storefront: Inertia.js 3 + Vue 3 (SSR), Tailwind CSS v4, FontAwesome
- PostgreSQL (shared), Redis
- Quality: Pest, Pint, PHPStan (level 5), 100% type coverage, ESLint + Prettier

## Database

`admin` is the single source of truth for the schema. The full table reference lives in:

- In-repo: `admin/docs/ShoFlow db doc.md` (and a copy under `shop/docs/`)
- Online: https://docs.google.com/document/d/e/2PACX-1vTqah2hdQeeiu3Le07zfOfp5vK-ojLwJtQmzbgdoq_wmJu-0dBdTcFsS0uSiUtYpSglEwMD5xSFIiG5/pub

Run migrations and seeders from `admin/` only. The storefront must not migrate these tables.

## Getting started

### 1. Docker environment files

Each compose file reads its own `.env`. Create all three from their examples:

```bash
cp infrastructure/docker/.env.example infrastructure/docker/.env
cp admin/docker/.env.example admin/docker/.env
cp shop/docker/.env.example shop/docker/.env
```

Fill in the blanks in `infrastructure/docker/.env` (database name, user, password,
Redis password) and set `USER_ID`/`GROUP_ID` to your own (`id -u`, `id -g`).

### 2. Start every container

The root `compose.yaml` merges the three compose files into one project, so a
single command from the repository root brings up the shared services and both
applications:

```bash
docker compose up -d --build
```

That starts six containers on a shared `shop_flow_net` network:

| Container | Role | Host port |
| --- | --- | --- |
| `shop_flow_db` | PostgreSQL 16 | `127.0.0.1:5432` |
| `shop_flow_redis` | Redis | `127.0.0.1:6379` |
| `shop_flow_admin_app` | admin PHP-FPM | — |
| `shop_flow_admin_nginx` | admin web server | `127.0.0.1:4040` |
| `shop_flow_shop_app` | storefront PHP-FPM | — |
| `shop_flow_shop_nginx` | storefront web server | `127.0.0.1:8080` |

Both apps wait for Postgres and Redis to report healthy before they start. Host
ports come from the `*_EXPOSE_PORT` variables in the three `.env` files.

Each app can still be started on its own — `docker compose up -d` inside
`infrastructure/docker`, `admin/docker`, or `shop/docker`. In that mode the
`infrastructure` project must come up first, because it creates the
`shop_flow_net` network that the other two join as an external network.

### 3. Configure each app

In both `admin/.env` and `shop/.env`, point the database at the shared Postgres (matching the values from `infrastructure/docker/.env`):

```bash
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
# DB_DATABASE / DB_USERNAME / DB_PASSWORD must match infrastructure/docker/.env
```

### 4. Admin (schema owner — set up first)

```bash
cd admin
composer install
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
```

### 5. Storefront

```bash
cd shop
composer install
php artisan key:generate
npm install && npm run build
```

For app-specific details (Docker containers, SSR, conventions), see each app's own `README.md`, `AGENTS.md`, and `docs/`.

## Production

`compose.yaml` is for development only — it bind-mounts the source and installs
dependencies on every container start. Production uses a separate stack,
`compose.prod.yaml`, which bakes the application into images, serves both apps
through Caddy with automatic TLS, and runs the Inertia renderer as its own
container.

Setup for an Ubuntu VPS is documented in
[`infrastructure/production/README.md`](infrastructure/production/README.md);
deploys run through `./infrastructure/production/deploy.sh`.

## Testing & quality

The storefront bundles all checks into one command (run inside its container):

```bash
cd shop
composer test-dev   # Pest, Pint, Pest type-coverage (--min=100), PHPStan, ESLint, Prettier
```

CI runs the same checks via `.github/workflows/deploy-application.yml`.

## Documentation

- `shop/docs/STOREFRONT_IMPLEMENTATION.md` — storefront roadmap and status
- `admin/docs/` and `shop/docs/` — schema, variety guide, orders/inventory, cache keys, tags
- `shop/AGENTS.md` / `admin/AGENTS.md` — conventions for contributors and AI agents

## License

Open-sourced under the [MIT license](LICENSE).
