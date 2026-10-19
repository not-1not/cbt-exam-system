# syntax=docker/dockerfile:1

###############################################################################
# Stage 1: build front-end assets with Node
###############################################################################
FROM node:22-alpine AS build

WORKDIR /app

COPY package.json package-lock.json .npmrc ./

RUN npm ci

COPY resources/ ./resources/
COPY public/ ./public/
COPY vite.config.js ./

RUN npm run build

###############################################################################
# Stage 2: PHP-FPM application image
###############################################################################
FROM php:8.4-fpm-alpine

# System libraries + PHP extensions (pgsql, redis, gd, intl, zip, ...)
RUN apk add --no-cache \
        postgresql-client \
        postgresql-dev \
        icu-dev \
        libzip-dev \
        freetype-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        intl \
        opcache \
        pdo_pgsql \
        pgsql \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis

ENV COMPOSER_ALLOW_SUPERUSER=1
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP dependencies first (better layer caching)
COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Application source
COPY . .

# Built front-end assets from the Node stage
COPY --from=build /app/public/build ./public/build

# Entrypoint (LF line endings, executable)
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Laravel writable directories
RUN mkdir -p \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000

ENTRYPOINT ["entrypoint.sh"]
