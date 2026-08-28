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

**The catalog cache is mirrored the same way, and shares one Redis store.** `app/Support/ProductCache.php`
plus the four observers in `app/Observers/` (`Product`, `Variety`, `Image`, `Review`) exist as one file
copied into each app: the storefront writes the entries, the panel deletes them when staff edit a
product, and both sides have to build the same keys. `shop/tests/Feature/ProductCacheMirrorTest.php`
compares PHP token streams (not bytes — the apps' Pint configs space concatenation differently) and
`CatalogCacheStoreTest.php` pins the shared store and prefixes. Change both copies in one commit; see
`CACHE.md`.

## Read the docs first

Before starting a task, read the ones relevant to it. They are duplicated in `admin/docs/` and
`shop/docs/` where both apps care:

| Doc | What it settles |
|---|---|
| `ShoFlow db doc.md` | schema reference, source of truth |
| `ORDER.md` | orders + inventory rules. **Stock is decremented only on successful payment** (Strategy A); carts never touch inventory |
| `CACHE.md` | the cache register. Products/varieties/category listings **are** cached (Redis, shared by both apps); everything else is still just an identified key |
| `admin/docs/IMPLEMENTATION.md` | admin build status |
| `shop/docs/STOREFRONT_IMPLEMENTATION.md` | storefront roadmap — update it as features land |
| `shop/docs/TAGS.md`, `BANNERS_SLIDERS.md`, `VARIETY_GUIDE.md`, `SHIPPING_GUIDE.md` | the tricky domains |
| `demo/README.md` | the demo/staging catalog dataset: what is tracked, and how to regenerate its images |

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

`migrate:fresh --seed` from `admin` also runs `DemoSeeder` in `local` and `staging` (never
production): ~50 demo products with variants, banners, sliders, tags and reviews, read from
`demo/data/*.json`. Its processed images are **not** in git — each image row is simply skipped when
the file is missing, so the catalog still seeds, just without pictures. See `demo/README.md` to
regenerate them.

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

## Traps that cost time

**The `deploy` user's login shell is fish, not bash.** A bash loop or `&&` chain
sent as an `ssh user@host '...'` argument is a syntax error there — it fails
*after* the earlier steps of a script have already run, which is the worst
moment. Always pipe remote scripts through bash explicitly:

```bash
ssh -p 9011 deploy@87.107.104.19 'bash -s' <<'EOF'
...
EOF
```

**`vendor/bin/pint --dirty` inside the container lints nothing and reports
"PASS ... 0 files".** `.git` is root-owned while the container runs as `www-data`
(uid 1000), so git aborts with `detected dubious ownership in repository at
'/var/www/html'`; Pint swallows that and finds no changed files. Every `--dirty`
"pass" is therefore vacuous — which is how files that fail `pint --test` reached
`main`. Either allow the directory once per container:

```bash
docker exec -u www-data shop_flow_admin_app git config --global --add safe.directory /var/www/html
```

or skip `--dirty` and pass the paths you touched explicitly, which is reliable:

```bash
docker exec -u www-data shop_flow_admin_app php -d memory_limit=1024M \
    vendor/bin/pint --test --format agent app/Support app/Observers
```

A bare `pint --test` over a whole app also dies on the container's 128 MB CLI
limit, so use `php -d memory_limit=1024M` and prefer per-directory runs.

**Do not pass a container path through `-e VAR=/tmp/...` from Git Bash.** MSYS
rewrites `/tmp/phpstan-www` into `C:/Users/<you>/AppData/Local/Temp/phpstan-www`
*before* docker sees it, so PHPStan created a literal `admin/C:/Users/...`
directory tree inside the repo (1500 files of untracked cache). The documented
PHPStan invocation is safe from PowerShell; from Git Bash prefix it with
`MSYS_NO_PATHCONV=1`, or set the variable inside the container instead
(`bash -lc 'TMPDIR=/tmp/phpstan-www ...'`). The same mangling puts psysh's
history there when passing `-e HOME=/tmp`.

**A cache the two apps are meant to share needs *four* settings to agree, not one.**
The catalog cache works only because both apps resolve the same Redis key, and
the final key is `{database.redis.options.prefix}{cache.prefix}{key}` — so
`CACHE_STORE`, `CACHE_PREFIX` **and** `REDIS_PREFIX` all have to match, in both
`.env` files *and* both config files' defaults. They did not: each app derived
both prefixes from `APP_NAME` through `Str::slug` with a different separator
(`_cache_` in admin, `-cache-` in the shop), and `admin/.env` had a bare
`CACHE_PREFIX=`, which Laravel reads as an empty string rather than falling back
to the default. Production was *already* `CACHE_STORE=redis` in both apps, so
this was live: every `Cache::forget()` in the panel would have succeeded and
cleared nothing, with no error anywhere. All four are now pinned literals, held
together by `shop/tests/Feature/CatalogCacheStoreTest.php`. When adding a cache
either app invalidates, verify the round trip rather than trusting the config —
write from one container and read from the other:

```bash
docker exec -u www-data -e HOME=/tmp shop_flow_admin_app php artisan tinker \
    --execute 'Cache::put("probe","x",60); echo config("cache.prefix");'
docker exec -u www-data -e HOME=/tmp shop_flow_shop_app php artisan tinker \
    --execute 'var_dump(Cache::get("probe"));'
```

(`HOME=/tmp` is needed or tinker dies on an unwritable psysh config dir and
prints nothing at all.)

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

**Run `docker compose` only from the repo root, never from `admin/docker/` or
`shop/docker/`.** Those leaf files are startable on their own, but each sets its
own `COMPOSE_PROJECT_NAME` (`shop_flow_admin`, `shop_flow_shop`) while the
container *names* are fixed — so a leaf invocation claims the root project's
container names under a different project, and each project then manages only
half the stack. The symptoms are a `Conflict. The container name
"/shop_flow_shop_app" is already in use`, or an nginx that came up without its
app and crash-loops on `host not found in upstream "admin_app"`. Recover by
`docker rm -f`-ing the containers by name and bringing the stack up from the root.

Related: `docker container prune -f` deletes *stopped* containers, and the whole
stack is briefly stopped after a Docker Desktop restart — pruning in that window
removes real stack containers. Re-check `docker ps -a` immediately before pruning,
not minutes earlier.

**A full host disk makes every writing Docker command fail with `read-only file
system`** — `docker system df` and `docker builder prune` included, so pruning is
not a way out. Free space on the host, then **restart Docker Desktop**: buildkit's
metadata DB stays mounted read-only until the VM restarts, so builds keep failing
on a disk that now has room. Worth knowing here because a production build
compiles five images' PHP extensions from source.

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
