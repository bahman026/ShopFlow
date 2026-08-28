# ShopFlow — project context for Claude

## What this is

ShopFlow is an open-source (MIT), **single-vendor** e-commerce platform for a Persian, RTL online
store — one business selling its own products. There is no marketplace, no sellers, no
seller-scoped anything: never add `seller_id` or a seller relation to a model, migration or
resource.

A monorepo of two Laravel 13 / PHP 8.5 apps sharing **one** PostgreSQL database:

| Path | App | Role |
|---|---|---|
| `admin/` | Laravel + Filament 5 | management panel. **Owns the database schema** — every migration lives here |
| `shop/` | Laravel + Inertia + Vue 3 (SSR) | customer-facing storefront. Mostly *reads* the catalog |

This file lives in `ai-context/claude/`, not at the repo root; `CLAUDE.md` at the root is a one-line
import of it. Per-app conventions are in `ai-context/claude/admin.md` and `ai-context/claude/shop.md`,
imported by `admin/CLAUDE.md` and `shop/CLAUDE.md` so they load only when that app is being worked
on. See `ai-context/README.md` for how the wiring works and why.

## The one rule that spans both apps

**`admin` owns the schema; `shop` never migrates it.** The storefront adds read-focused Eloquent
models mapping to the same tables. A schema change is an `admin/` migration, then the shop model is
updated to match — never the other way round, and never a duplicate table.

`admin/docs/ShoFlow db doc.md` (copied to `shop/docs/`) is the source of truth for columns and
relationships. Read it before assuming a column exists or is non-null; a column the primary flow
always sets can still be nullable at the DB level (`users.mobile` is, and has thrown a `TypeError`
for exactly that reason).

**Enums are mirrored, not shared.** Every enum in `shop/app/Enums` has a twin in `admin/app/Enums`
with the same case names and backing values — the values are what the two apps agree on through the
database, so changing one side alone silently breaks the other. `shop/tests/Feature/EnumMirrorTest.php`
fails the build on any mismatch. Change both in the same commit.

## Read the docs first

Before starting a task, read the ones relevant to it. They are duplicated in `admin/docs/` and
`shop/docs/` where both apps care:

| Doc | What it settles |
|---|---|
| `ShoFlow db doc.md` | schema reference, source of truth |
| `ORDER.md` | orders + inventory rules. **Stock is decremented only on successful payment** (Strategy A); carts never touch inventory |
| `CACHE.md` | cache keys identified; nothing is cached yet |
| `admin/docs/IMPLEMENTATION.md` | admin build status |
| `shop/docs/STOREFRONT_IMPLEMENTATION.md` | storefront roadmap — update it as features land |
| `shop/docs/TAGS.md`, `BANNERS_SLIDERS.md`, `VARIETY_GUIDE.md`, `SHIPPING_GUIDE.md` | the tricky domains |

## Local development

Both apps run in Docker. One `docker compose up -d --build` from the repo root brings up all six
containers (the root `compose.yaml` merges the three app compose files):

| Container | Role | Host port |
|---|---|---|
| `shop_flow_db` | PostgreSQL 16 | `127.0.0.1:5432` |
| `shop_flow_redis` | Redis | `127.0.0.1:6379` |
| `shop_flow_admin_app` / `_nginx` | admin | `127.0.0.1:4040` |
| `shop_flow_shop_app` / `_nginx` | storefront | `127.0.0.1:8080` |

Run everything inside the container as the web user:

```bash
docker exec -it -u www-data shop_flow_admin_app bash
docker exec -u www-data shop_flow_admin_app php artisan migrate
```

Each compose file reads its own `.env` (`infrastructure/docker/`, `admin/docker/`, `shop/docker/`) —
copy from `.env.example` and set `USER_ID`/`GROUP_ID` to your own.

**Trap: the container's CLI `memory_limit` is 128 MB**, which is not enough for the admin app's full
Pest run — it dies mid-suite with `Allowed memory size exhausted` inside a Filament view. That is not
a broken test. Run the full suite with an override:

```bash
docker exec -u www-data shop_flow_admin_app php -d memory_limit=1024M vendor/bin/pest
```

The storefront's `--type-coverage` run hits the same ceiling (it loads PHPStan),
and PHPStan there also needs a writable cache dir as `www-data`:

```bash
docker exec -u www-data -e TMPDIR=/tmp/phpstan-www shop_flow_shop_app \
    bash -lc 'mkdir -p /tmp/phpstan-www && php -d memory_limit=1024M vendor/bin/pest --type-coverage --min=100'
```

## Testing

Use Pest directly, never `php artisan test`.

```bash
# admin — Pest, Pint, type coverage, PHPStan
docker exec -u www-data shop_flow_admin_app composer test-dev
# storefront — the same four plus ESLint and Prettier
docker exec -u www-data shop_flow_shop_app composer test-dev
```

Both must pass before finishing. 100% type coverage is required in `shop/`; PHPStan runs at level 5;
Pint formats every PHP file (`vendor/bin/pint --dirty --format agent` after editing PHP).

**The storefront tests run against a real Postgres database (`shop_flow_test`) whose schema is built
by `admin`'s migrations**, not sqlite and not the shop's own — the shop does not own those tables, and
sqlite would hide schema drift. One-time setup, from the admin container:

```bash
DB_DATABASE=shop_flow_test php artisan migrate --force
DB_DATABASE=shop_flow_test php artisan db:seed --class="Database\Seeders\SettingSeeder" --force
```

After a new admin migration, re-run the first line against `shop_flow_test` too, or the storefront
suite fails against a stale schema.

## Production

`compose.yaml` is development only. Production is `compose.prod.yaml` — images with the app baked in,
Caddy as the only container with published ports. **Read
`infrastructure/production/README.md` before touching any of it**; the deployment does not work the
way the stock recipe implies:

- The server cannot reach Docker Hub, `deb.debian.org`, packagist, the npm registry or Let's
  Encrypt's ACME API, so images **cannot be built on it**. They are cross-built for `linux/amd64`
  elsewhere and shipped with `infrastructure/production/ship-images.sh`, then deployed with
  `deploy-prebuilt.sh`. A registry mirror does not fix this — the PHP base stage still needs Debian apt.
- TLS is a certificate obtained off-box via a DNS-01 challenge and mounted into Caddy; automatic
  HTTPS is switched off. See `infrastructure/production/certs/README.md` for renewal.
- The first admin user is created by `AdminSeeder` (which assigns the `super-admin` role), **not**
  `make:filament-user` — the panel gate `canAccessPanel()` requires a role, and that command assigns
  none, so the account it creates cannot log in.

## Two traps that cost time

**The `deploy` user's login shell is fish, not bash.** A bash loop or `&&` chain
sent as an `ssh user@host '...'` argument is a syntax error there — it fails
*after* the earlier steps of a script have already run, which is the worst
moment. Always pipe remote scripts through bash explicitly:

```bash
ssh -p 9011 deploy@87.107.104.19 'bash -s' <<'EOF'
...
EOF
```

**Filament caches the resources and pages it discovered** in
`bootstrap/cache/filament`. While that cache exists a newly added resource or
page is invisible in the sidebar no matter how correct the code is — and tests
still pass, because the test process builds its own container without it. If
something new does not appear in the panel, clear it before doubting the code:

```bash
docker exec -u www-data shop_flow_admin_app php artisan filament:optimize-clear
```

The production entrypoint runs `filament:optimize`, which rebuilds this cache on
every container start — correct there, and not a problem, since a new image
starts with a fresh one.

## Commits

- **Never commit or push without being asked in that message.** Finishing work is not permission.
- Author: `Bahman026 <bahman026@gmail.com>`.
- **Conventional Commits** (`feat`, `fix`, `chore`, `build`, `refactor`, `docs`, `style`, `test`),
  scoped `admin`/`shop`/`infra` where it helps.
- One logical change per commit; do not bundle unrelated changes. Check `git diff --cached` before
  committing — `git commit` commits the whole index, not just what you last `git add`ed.
- Documentation duplicated across both apps (`ORDER.md`, `ShoFlow db doc.md`) is updated in both
  copies in the same commit.

## Keeping this current

When a task turns up something that would have saved time — a trap, an upstream behaviour, a decision
and its reason — write it down before finishing: repo-wide here, app-specific in
`ai-context/claude/admin.md` or `shop.md`. Leave out what the code, `git log` or `docs/` already
answers; every line here is loaded into a session.
