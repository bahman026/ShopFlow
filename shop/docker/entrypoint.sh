#!/bin/bash

set -e
# --system rather than --global: composer runs as www-data below, and a
# --global exception written by root only covers root. /etc/gitconfig is
# readable by every user, so one line covers both.
git config --system --add safe.directory /var/www/html

# composer caches into $HOME/.composer, and www-data's home (/var/www) is
# root-owned, so that user cannot create the directory itself. Without this
# composer still works but rebuilds its cache from scratch every time.
install -d -o www-data -g www-data /var/www/.composer

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
