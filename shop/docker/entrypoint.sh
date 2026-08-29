#!/bin/bash

set -e
git config --global --add safe.directory /var/www/html

# As www-data, not root: php-fpm and the test suite both run as www-data, and
# Pest writes caches *inside* vendor/ (pest/.temp, plus the type-coverage and
# mutate plugins). A root-owned vendor/ makes those fail with "Permission
# denied" — a warning in a normal run, but fatal under `pest --parallel`.
runuser -u www-data -- composer install

# Self-heal a vendor/ installed as root before the line above existed.
chown -R www-data:www-data /var/www/html/shop/vendor/pestphp 2>/dev/null || true

php "/var/www/html/shop/artisan" migrate --force

# Create storage symlinks (ignore if it already exists)
php "/var/www/html/shop/artisan" storage:link || true

# Build Inertia front-end assets (skip if no package.json yet)
if [ -f "/var/www/html/shop/package.json" ]; then
    npm install
    npm run build
fi

# Refresh caches
php "/var/www/html/shop/artisan" optimize:clear

# Start the Inertia SSR server in the background (Vue server-side rendering)
php "/var/www/html/shop/artisan" inertia:start-ssr &

# Start php-fpm
exec php-fpm
