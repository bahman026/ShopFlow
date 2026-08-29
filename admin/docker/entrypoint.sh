#!/bin/bash

set -e
git config --global --add safe.directory /var/www/html

# As www-data, not root: php-fpm and the test suite both run as www-data, and
# Pest writes caches *inside* vendor/ (pest/.temp, plus the type-coverage and
# mutate plugins). A root-owned vendor/ makes those fail with "Permission
# denied" — a warning in a normal run, but fatal under `pest --parallel`.
runuser -u www-data -- composer install

# Self-heal a vendor/ installed as root before the line above existed.
chown -R www-data:www-data /var/www/html/admin/vendor/pestphp 2>/dev/null || true

php "/var/www/html/admin/artisan" migrate --force

# Create storage symlinks (ignore if it already exists)
php "/var/www/html/admin/artisan" storage:link || true

# Refresh caches
php "/var/www/html/admin/artisan" optimize:clear
php "/var/www/html/admin/artisan" filament:optimize

# Start php-fpm
exec php-fpm
