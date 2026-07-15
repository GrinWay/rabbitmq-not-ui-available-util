FROM dunglas/frankenphp:1-builder-php8.5-alpine

WORKDIR /app

RUN install-php-extensions \
    xdebug

RUN rm -f /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN apk update && \
    apk add --no-cache netcat-openbsd

COPY ./composer.lock ./composer.json  ./composer.phar .

RUN php ./composer.phar install \
    --no-interaction \
    --no-progress \
    --prefer-dist
