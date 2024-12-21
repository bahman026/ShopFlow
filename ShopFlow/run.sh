#!/bin/bash

composer install
npm i
npm run build
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
chmod -R +x /var/www/html/storage/app
php-fpm -F
