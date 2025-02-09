FROM php:7.4-fpm-alpine

# PDO Mysql
RUN docker-php-ext-install pdo_mysql

# Redis
RUN apk add --no-cache pcre-dev $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis

# OPCache
RUN docker-php-ext-install opcache

# Cleanup
RUN rm -rf /tmp/* \
    && rm -rf /var/cache/apk/* \
    && rm -rf tmp/*

WORKDIR /var/www
CMD wait-for-it database:3306 ; php-fpm

EXPOSE 9000
