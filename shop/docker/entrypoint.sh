#!/bin/bash

set -e
git config --global --add safe.directory /var/www/html

composer install

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

# Start php-fpm
exec php-fpm
