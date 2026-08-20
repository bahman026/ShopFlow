# Deploying ShopFlow to an Ubuntu VPS

The production stack is `compose.prod.yaml` at the repository root. It is
separate from the development `compose.yaml` and shares nothing with it: the
application source is baked into images instead of bind-mounted, Postgres and
Redis publish no host ports, and Caddy is the only container listening on the
public interface.

```
                     :80 :443
                        │
                    ┌───▼───┐
                    │ Caddy │  automatic Let's Encrypt TLS
                    └─┬───┬─┘
        admin.example │   │ shop.example
                 ┌────▼┐ ┌▼────┐
                 │admin│ │shop │   nginx, static files only
                 │_web │ │_web │
                 └──┬──┘ └──┬──┘
                    │fastcgi│
                 ┌──▼──┐ ┌──▼──┐   ┌─────────┐
                 │admin│ │shop │   │shop_ssr │  Inertia renderer (node)
                 │_app │ │_app │◄──┤         │
                 └──┬──┘ └──┬──┘   └─────────┘
                    └───┬───┘
                  ┌─────▼─────┐
                  │  db  redis│  named volumes, no published ports
                  └───────────┘
```

## What you have to do

A first deployment, in order. Each step is detailed below.

1. Point DNS for two hostnames at the server — **do this first**, Caddy needs it
   to work before it can get certificates.
2. Create a VPS: Ubuntu 24.04, 2 GB RAM minimum (4 GB recommended), 20 GB disk.
3. Install Docker from Docker's own apt repository.
4. Create a non-root `deploy` user, enable `ufw`, turn off SSH passwords.
5. Clone the repo as `deploy`.
6. Fill in three env files: `.env.production`, `admin/.env.production`,
   `shop/.env.production`.
7. Run `./infrastructure/production/deploy.sh`.
8. Create the first admin user.
9. Set up database and uploads backups — nothing here does that for you.

Redeploys after that are `git pull && ./infrastructure/production/deploy.sh`.

If the server cannot reach Docker Hub, `deb.debian.org`, packagist or the npm
registry, none of that works and steps 3, 5 and 7 change — see
[Building somewhere else](#building-somewhere-else) below.

## 1. DNS

Create `A` records (and `AAAA` if you have IPv6) for the storefront and the
panel, both pointing at the server's IP:

```
shop.example.com    A    203.0.113.10
admin.example.com   A    203.0.113.10
```

Caddy requests certificates on first boot over HTTP-01, so these have to resolve
and ports 80/443 must be reachable from the internet. If DNS is not ready, the
stack still comes up but Caddy will keep retrying and the sites serve TLS errors.

## 2. Server

- Ubuntu 24.04 LTS.
- **2 GB RAM minimum, 4 GB recommended.** The storefront's Vite build (browser
  bundle plus SSR bundle) is the memory peak. On a 2 GB box add swap first:

  ```bash
  sudo fallocate -l 2G /swapfile && sudo chmod 600 /swapfile
  sudo mkswap /swapfile && sudo swapon /swapfile
  echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
  ```

  Or build the images elsewhere and push them to a registry.
- 20 GB disk or more — images, the Postgres volume, and uploads all live here.

## 3. Install Docker

Use Docker's own apt repository, not Ubuntu's `docker.io` package — the latter
lags and does not ship the Compose v2 plugin this setup needs.

```bash
sudo apt-get update
sudo apt-get install -y ca-certificates curl
sudo install -m 0755 -d /etc/apt/keyrings
sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg \
    -o /etc/apt/keyrings/docker.asc
sudo chmod a+r /etc/apt/keyrings/docker.asc
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] \
https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo $VERSION_CODENAME) stable" \
    | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
sudo apt-get update
sudo apt-get install -y docker-ce docker-ce-cli containerd.io \
    docker-buildx-plugin docker-compose-plugin
```

Check it: `docker compose version` should report v2.x.

## 4. Harden the box

```bash
# Run the stack as a non-root user.
sudo adduser --disabled-password --gecos "" deploy
sudo usermod -aG docker deploy
sudo rsync --archive --chown=deploy:deploy ~/.ssh /home/deploy   # copy your key

# Only SSH and the web ports.
sudo ufw allow OpenSSH
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable

# Security updates without a login.
sudo apt-get install -y unattended-upgrades
sudo dpkg-reconfigure -plow unattended-upgrades
```

Then set `PasswordAuthentication no` in `/etc/ssh/sshd_config` and
`sudo systemctl restart ssh`, once you have confirmed key login works.

Note that Docker writes iptables rules that bypass ufw for *published* ports.
That is harmless here because only Caddy publishes anything — but keep it in mind
before adding `ports:` to another service.

## 5. Clone

```bash
sudo -iu deploy
git clone https://github.com/bahman026/ShopFlow.git
cd ShopFlow
```

## 6. Configure

```bash
cp .env.production.example .env.production
cp admin/.env.production.example admin/.env.production
cp shop/.env.production.example shop/.env.production
```

Generate the secrets:

```bash
openssl rand -base64 32                    # POSTGRES_PASSWORD
openssl rand -base64 32                    # REDIS_PASSWORD
echo "base64:$(openssl rand -base64 32)"   # APP_KEY for admin
echo "base64:$(openssl rand -base64 32)"   # APP_KEY for shop — a different one
```

Then fill in all three files. Values that must not be left at their defaults:

| File | Key | Notes |
| --- | --- | --- |
| `.env.production` | `POSTGRES_PASSWORD`, `REDIS_PASSWORD` | From above |
| `.env.production` | `SHOP_DOMAIN`, `ADMIN_DOMAIN`, `LETSENCRYPT_EMAIL` | Real hostnames from step 1 |
| `admin/.env.production` | `APP_KEY` | `base64:...` — required, the container refuses to start without it |
| `shop/.env.production` | `APP_KEY` | A **different** key from admin's |
| both app files | `DB_PASSWORD`, `REDIS_PASSWORD` | Must match `.env.production` |
| both app files | `APP_URL` | `https://` plus the real hostname |
| `shop/.env.production` | `IMAGE_URL` | `https://<admin domain>/storage` — product images are served by the panel |
| both app files | `MAIL_*` | Password resets and order mail need a real SMTP host |
| `shop/.env.production` | `ZARINPAL_MERCHANT_ID`, `NESHAN_*` | Live payment and map credentials |

Two things worth knowing:

- The Postgres credentials in `.env.production` are only applied when the
  `pgdata` volume is first created. Changing them later does **not** change the
  existing role — you have to `ALTER ROLE` by hand.
- The app env files are passed to the containers as environment variables; there
  is no `.env` inside the images. The entrypoint runs `config:cache` at startup,
  so any change here needs a container restart to take effect.

## 7. Deploy

```bash
./infrastructure/production/deploy.sh
```

The script:

1. checks the three env files exist,
2. tags the images with the current commit SHA,
3. builds all five images,
4. starts Postgres and Redis and waits for them to report healthy,
5. runs `php artisan migrate --force` **once** from a throwaway admin
   container — admin owns the shared schema, the storefront never migrates it,
6. replaces the long-running containers and waits for their healthchecks,
7. prunes superseded images.

Migrations are deliberately not part of container startup. Doing them there
would make every restart depend on the database being reachable and would let
two containers race each other on the schema.

First build takes 5–15 minutes (PHP extensions are compiled from source).
Later builds reuse the cache and are much faster.

## 8. First admin user

Not `make:filament-user`. The panel gate is `User::canAccessPanel()`, which
requires the `super-admin` or `admin` role, and that command assigns neither —
the account it creates exists and cannot log in. Use `AdminSeeder`, which
creates the user *and* gives it `super-admin`.

Set the four `ADMIN_*` values in `admin/.env.production` first, or the account
is created with the `config/admin.php` defaults — `admin@shopFlow.dev` with the
password `password`. Then:

```bash
docker compose -f compose.prod.yaml --env-file .env.production \
    run --rm --no-deps admin_app \
    php artisan db:seed --class="Database\Seeders\AdminSeeder" --force
```

It is `firstOrCreate` on the email, so re-running it is harmless — but note that
it will not change the password of an account that already exists.

Then log in at `https://admin.example.com/admin`.

## 9. Backups

Nothing here backs anything up. Two things cannot be rebuilt from git — the
database and the uploads volume:

```bash
# Database
docker compose -f compose.prod.yaml --env-file .env.production \
    exec -T db pg_dump -U shop_flow shop_flow | gzip > db-$(date +%F).sql.gz

# Uploads
docker run --rm -v shopflow_prod_admin_storage:/data -v "$PWD":/backup \
    alpine tar czf /backup/uploads-$(date +%F).tar.gz -C /data .
```

Put both in a cron job that copies the archives *off* the server. A backup on
the same disk is not a backup.

## Building somewhere else

`deploy.sh` builds on the server, and each build stage reaches out to the
network: Docker Hub for the base images, `deb.debian.org` for the libraries the
PHP extensions link against, packagist for `composer install`, the npm registry
for `npm ci`. Where those are filtered — an Iranian IP, for one — the build dies
at the first `apt-get update` inside `php:8.5-fpm-bookworm`. A registry mirror
in `/etc/docker/daemon.json` does not rescue it: that fixes only the base image
pull, and the three later stages still have nowhere to fetch from.

Build on a workstation instead and ship the result. No registry is involved.

```bash
# On the workstation, from the repository root:
./infrastructure/production/ship-images.sh deploy@203.0.113.10 9011

# Then on the server:
cd ~/ShopFlow
IMAGE_TAG=<sha> ./infrastructure/production/deploy-prebuilt.sh
```

`ship-images.sh` builds the five images from a clean `git archive` of `HEAD`
(not the working tree — the tag has to mean something for a rollback), pulls
`postgres`/`redis`/`caddy` alongside them, checks every one is `linux/amd64`,
and streams all eight through `docker save | ssh | docker load` as a single
tarball so the two php-fpm images share their base layers instead of carrying
them twice.

The architecture check is the part not to remove. A plain `docker image
inspect` resolves a multi-platform image to the *host* variant, so on an Apple
Silicon machine the pulled `redis:7-alpine` reports `arm64` and looks fine;
`--platform` is what makes the check mean anything. Ship the wrong variant and
the containers crash-loop with `exec format error` long after the cause has
scrolled off screen.

`deploy-prebuilt.sh` is `deploy.sh` with the build dropped and `--pull never`
added, so a missing tag fails immediately instead of hanging on a registry that
will never answer. Migrations still run once from a throwaway container.

Only the deploy files need to reach the server — `compose.prod.yaml`, the
`Caddyfile`, `deploy-prebuilt.sh` and the three env files. The app source does
not: Compose never stats a build context it is not building.

### TLS when ACME is unreachable

Caddy cannot issue a certificate on a host that cannot reach
`acme-v02.api.letsencrypt.org`. Set `SITE_SCHEME=http://` in `.env.production`
and it serves plain HTTP and asks for no certificate, leaving TLS to a CDN in
front — Cloudflare, ArvanCloud — which reaches the origin over port 80. The
apps still generate `https://` URLs, because the CDN's `X-Forwarded-Proto`
survives the extra hop and `trustProxies` in both `bootstrap/app.php` files
honours it. Keep `APP_URL` on `https://` and `SESSION_SECURE_COOKIE=true`: the
browser's half of the connection is TLS even though this hop is not.

Two consequences worth stating. Anyone who knows the origin IP can reach the
site over plain HTTP with a spoofed `Host` header and bypass the CDN entirely,
so an origin allowlist of the CDN's ranges belongs on the follow-up list. And
with Cloudflare specifically, "Flexible" mode is the quick version; a Cloudflare
Origin Certificate mounted into Caddy plus "Full (strict)" is the one to end up
on, and it needs no ACME access.

## Redeploying

```bash
cd ~/ShopFlow
git pull
./infrastructure/production/deploy.sh
```

### Rolling back

Images are tagged with the commit they were built from, so a rollback needs no
rebuild:

```bash
IMAGE_TAG=<old-sha> docker compose -f compose.prod.yaml \
    --env-file .env.production up -d
```

A migration that has already run is not undone by this. If the bad deploy
changed the schema, restore the database dump as well.

## Day-to-day operations

```bash
# Worth putting in ~/.bashrc
alias dcp='docker compose -f compose.prod.yaml --env-file .env.production'

dcp ps                      # health of every container
dcp logs -f shop_app        # follow one service
dcp exec admin_app bash     # shell in the panel
dcp restart shop_ssr        # bounce the renderer
dcp exec admin_app php artisan tinker
```

Logs go to stdout/stderr and are capped at 10 MB × 5 files per container, so
there is nothing on disk to rotate.

### Queue workers and the scheduler

Nothing in the codebase queues a job or registers a scheduled task yet, so those
containers sit behind a Compose profile and do not start by default. When you add
the first one, set `QUEUE_CONNECTION=redis` in both app env files, then:

```bash
dcp --profile workers up -d
```

## Troubleshooting

| Symptom | Cause |
| --- | --- |
| Caddy logs `could not get certificate` | DNS not pointing here yet, or 80/443 blocked upstream |
| `FATAL: APP_KEY is not set` | Missing `APP_KEY` in that app's `.env.production` |
| Storefront renders but unstyled | Asset build failed; check `dcp logs shop_web` and rebuild |
| Pages render client-side only | `shop_ssr` is unhealthy — `dcp logs shop_ssr` |
| Product images 404 | `IMAGE_URL` does not match the admin domain, or the uploads volume is not mounted |
| Panel redirects to `http://` | `APP_URL` is not `https://` |
| Build killed during `npm run build` | Out of RAM — add swap (step 2) |
| Containers exit with `exec format error` | Images built for the wrong architecture — see [Building somewhere else](#building-somewhere-else) |
| `apt-get update` fails in the PHP base stage | The host cannot reach `deb.debian.org`; build elsewhere |

## Known follow-ups

- **`route:cache` is disabled for admin.** `admin/routes/web.php` defines two
  closure routes, which Laravel cannot serialize. Convert them to controller
  actions and enable it in `admin/docker/production/entrypoint.sh`.
- **OPcache JIT is off.** The setting is present but disabled in
  `<app>/docker/production/php.ini`; benchmark before enabling.
- **`pm.max_children = 20`** in `fpm-pool.conf` assumes a mid-size box. Each
  worker may use up to the 512 MB `memory_limit`; size it against real RAM.
- **No CI build.** `.github/workflows/deploy-application.yml` only runs tests.
  Building on the VPS is fine for one server; pushing to a registry becomes
  worthwhile at two.
- **Single host, no zero-downtime.** `deploy.sh` replaces containers in place, so
  there is a few-second gap. Fine for one VPS; a rolling setup needs a second
  host or a blue/green proxy config.
