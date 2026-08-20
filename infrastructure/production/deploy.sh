#!/usr/bin/env bash
#
# Deploy ShopFlow on the production VPS. Run from the repository root:
#
#   ./infrastructure/production/deploy.sh
#
# Order matters: images are built before anything is stopped, migrations run
# once from a throwaway container rather than from every app container's
# startup, and only then are the long-running containers replaced.
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

# Tag the images with the commit being deployed so a rollback has a target.
if [ -z "${IMAGE_TAG:-}" ] && git rev-parse --git-dir > /dev/null 2>&1; then
    IMAGE_TAG="$(git rev-parse --short HEAD)"
    export IMAGE_TAG
fi
echo "==> deploying ${IMAGE_TAG:-latest}"

echo "==> building images"
compose build

echo "==> starting database and cache"
compose up -d --wait db redis

# Only admin migrates: it owns the shared schema. --force skips the interactive
# confirmation that a non-tty deploy cannot answer.
echo "==> running migrations (admin owns the schema)"
compose run --rm --no-deps admin_app php artisan migrate --force

# Every Filament resource is gated by a permission row, so these have to exist
# before the panel is usable — without them staff log in to an empty panel with
# no resources at all. RolePermissionSeeder is idempotent (findOrCreate +
# syncPermissions), so running it on every deploy is safe and also picks up any
# permission added since the last release.
echo "==> syncing roles and permissions"
compose run --rm --no-deps admin_app php artisan db:seed --class="Database\\Seeders\\RolePermissionSeeder" --force

echo "==> replacing application containers"
compose up -d --build --remove-orphans --wait

echo "==> reclaiming disk from superseded images"
docker image prune -f

echo
compose ps
echo
echo "done. Logs: docker compose -f $COMPOSE_FILE --env-file $ENV_FILE logs -f"
