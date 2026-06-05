FROM node:16-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json webpack.mix.js ./
COPY resources ./resources
COPY public ./public

RUN npm install --legacy-peer-deps && npm run production

FROM composer:2 AS vendor

WORKDIR /app

COPY . .
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --ignore-platform-reqs

FROM php:8.1-apache

WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libicu-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-install intl pdo_mysql zip \
    && a2enmod rewrite \
    && printf "ServerName localhost\n" > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername \
    && rm -rf /var/lib/apt/lists/*

COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint

COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public ./public

RUN chmod +x /usr/local/bin/docker-entrypoint \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80

ENTRYPOINT ["docker-entrypoint"]
CMD ["apache2-foreground"]
