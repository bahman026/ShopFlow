#!/usr/bin/env bash
#
# Deploy from images that are already in the local image store, without
# building anything. Run from the deploy directory on the server:
#
#   IMAGE_TAG=<sha> ./infrastructure/production/deploy-prebuilt.sh
#
# The counterpart to ship-images.sh, for a server that cannot build its own
# images because Docker Hub, deb.debian.org, packagist and the npm registry are
# all filtered from it. Everything else matches deploy.sh: migrations run once
# from a throwaway container, and only then are the long-running containers
# replaced.
#
# Nothing here reaches the network. `--pull never` is deliberate: without it a
# missing tag turns into a several-minute registry timeout instead of an
# immediate, readable error.
set -euo pipefail

cd "$(dirname "$0")/../.."

COMPOSE_FILE="compose.prod.yaml"
ENV_FILE=".env.production"

compose() {
    docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" "$@"
}

for f in "$ENV_FILE" admin/.env.production shop/.env.production; do
    if [ ! -f "$f" ]; then
        echo "missing $f — copy it from ${f%.production}.production.example and fill it in" >&2
        exit 1
    fi
done

if [ -z "${IMAGE_TAG:-}" ]; then
    IMAGE_TAG="$(grep -E '^IMAGE_TAG=' "$ENV_FILE" | cut -d= -f2-)"
fi
if [ -z "${IMAGE_TAG:-}" ]; then
    echo "IMAGE_TAG is not set and $ENV_FILE does not define one" >&2
    exit 1
fi
export IMAGE_TAG

echo "==> deploying ${IMAGE_TAG} from local images"

missing=0
for img in admin-app admin-web shop-app shop-web shop-ssr; do
    if ! docker image inspect "shopflow/${img}:${IMAGE_TAG}" > /dev/null 2>&1; then
        echo "missing image shopflow/${img}:${IMAGE_TAG}" >&2
        missing=1
    fi
done
if [ "$missing" -ne 0 ]; then
    echo "run ship-images.sh from a workstation first" >&2
    exit 1
fi

echo "==> starting database and cache"
compose up -d --pull never --wait db redis

# Only admin migrates: it owns the shared schema. --force skips the interactive
# confirmation that a non-tty deploy cannot answer.
echo "==> running migrations (admin owns the schema)"
compose run --rm --no-deps admin_app php artisan migrate --force

# Every Filament resource is gated by a permission row, so these have to exist
# before the panel is usable. RolePermissionSeeder is idempotent, so running it
# on every deploy is safe and picks up any permission added since the last one.
echo "==> syncing roles and permissions"
compose run --rm --no-deps admin_app php artisan db:seed --class="Database\\Seeders\\RolePermissionSeeder" --force

echo "==> replacing application containers"
compose up -d --pull never --remove-orphans --wait

echo "==> reclaiming disk from superseded images"
docker image prune -f

echo
compose ps
echo
echo "done. Logs: docker compose -f $COMPOSE_FILE --env-file $ENV_FILE logs -f"
