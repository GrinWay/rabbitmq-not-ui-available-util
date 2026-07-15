FROM dunglas/frankenphp:1-builder-php8.5-alpine

WORKDIR /app

RUN install-php-extensions \
    xdebug \
    sockets

RUN rm -f /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN apk update && \
    apk add --no-cache netcat-openbsd

COPY --from=composer:2.8.12 /usr/bin/composer /usr/local/bin/composer

COPY ./composer.json ./composer.lock .

RUN composer install \
    --no-interaction \
    --no-progress \
    --prefer-dist
