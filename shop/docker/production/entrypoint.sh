#!/bin/bash
# Production entrypoint.
#
# Deliberately does NOT run composer install, npm build, or migrations: the
# first two happen at image build time, and the storefront never migrates the
# shared schema — admin owns it (see infrastructure/production/deploy.sh).
set -euo pipefail

if [ -z "${APP_KEY:-}" ]; then
    echo "FATAL: APP_KEY is not set. Generate one with 'php artisan key:generate --show'." >&2
    exit 1
fi

# Build the caches rather than clear them. Cheap, offline, and has to happen
# here rather than at build time because it bakes in the runtime environment.
php artisan config:cache
php artisan route:cache
php artisan event:cache
php artisan view:cache

exec "$@"
