#!/bin/bash
# Production entrypoint.
#
# Deliberately does NOT run composer install, npm build, or migrations: the
# first two happen at image build time, and migrations are a release step run
# once per deploy (see infrastructure/production/deploy.sh). Doing them here
# would make every container start depend on the network and would let two
# replicas race each other on the schema.
set -euo pipefail

if [ -z "${APP_KEY:-}" ]; then
    echo "FATAL: APP_KEY is not set. Generate one with 'php artisan key:generate --show'." >&2
    exit 1
fi

# Build the caches rather than clear them. Cheap, offline, and has to happen
# here rather than at build time because it bakes in the runtime environment.
php artisan config:cache
php artisan event:cache
php artisan view:cache
# route:cache is skipped on purpose: routes/web.php still defines two closure
# routes, which Laravel cannot serialize. Convert them to controller actions
# and this can be enabled.
php artisan filament:optimize

exec "$@"
