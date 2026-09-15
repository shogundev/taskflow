FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
        libpng-dev \
        libzip-dev \
        oniguruma-dev \
        icu-dev \
        $PHPIZE_DEPS \
    && docker-php-ext-install pdo_mysql mbstring bcmath gd zip opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

RUN addgroup -g 1000 www && adduser -G www -u 1000 -D www
USER www

EXPOSE 9000
CMD ["php-fpm"]
