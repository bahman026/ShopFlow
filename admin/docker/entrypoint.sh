#!/bin/bash

set -e
# Migrate and seed database
composer  install
php "/var/www/html/admin/artisan" migrate --seed --force

php "/var/www/html/admin/artisan" vendor:publish --tag=filament-tables-views --force
# Refresh caches
php "/var/www/html/admin/artisan" optimize:clear
php "/var/www/html/admin/artisan" optimize
php "/var/www/html/admin/artisan" icon:cache
php "/var/www/html/admin/artisan" filament:cache-components

# Create storage symlinks
php "/var/www/html/admin/artisan" storage:link

# Start Supervisor
#exec supervisord -c /etc/supervisor/supervisord.conf
exec php-fpm
