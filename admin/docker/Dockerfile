FROM php:8.3-fpm

ARG uid
ARG gid

RUN apt-get update -y
RUN apt-get install -y git
RUN apt-get install -y curl
RUN apt-get install -y libpng-dev
RUN apt-get install -y libpq-dev
RUN apt-get install -y libonig-dev
RUN apt-get install -y libxml2-dev
RUN apt-get install -y zip
RUN apt-get install -y unzip
RUN apt-get install -y libzip-dev
RUN apt-get install -y libwebp-dev
RUN apt-get install -y libjpeg62-turbo-dev
RUN apt-get install -y libxpm-dev
RUN apt-get install -y libfreetype6-dev

RUN docker-php-ext-configure gd \
    --enable-gd \
    --with-webp=/usr/include/ \
    --with-jpeg=/usr/include/ \
    --with-xpm=/usr/include/ \
    --with-freetype=/usr/include

RUN docker-php-ext-install pgsql
RUN docker-php-ext-install pdo_pgsql
RUN docker-php-ext-install mbstring
RUN docker-php-ext-install exif
RUN docker-php-ext-install pcntl
RUN docker-php-ext-install bcmath
RUN docker-php-ext-install gd
RUN docker-php-ext-install zip
RUN docker-php-ext-install intl
RUN pecl install redis && docker-php-ext-enable redis

RUN curl -sL https://deb.nodesource.com/setup_20.x | bash -
RUN apt-get install -y nodejs
RUN npm install pm2 -g


COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN usermod -u $uid www-data
RUN groupmod -g $gid www-data

RUN mkdir -p "/var/www/.npm"
RUN chown -R $uid:$gid "/var/www/.npm"

RUN mkdir -p "/var/www/.pm2"
RUN chown -R $uid:$gid "/var/www/.pm2"

WORKDIR /var/www/html/admin
