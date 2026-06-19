#!/bin/bash

set -e
git config --global --add safe.directory /var/www/html

composer install

php "/var/www/html/admin/artisan" migrate --force

# Create storage symlinks (ignore if it already exists)
php "/var/www/html/admin/artisan" storage:link || true

# Refresh caches
php "/var/www/html/admin/artisan" optimize:clear
php "/var/www/html/admin/artisan" filament:optimize

# Start php-fpm
exec php-fpm
