FROM php:7.4-fpm-alpine

COPY wait-for-it.sh /usr/bin/wait-for-it
RUN chmod +x /usr/bin/wait-for-it
RUN apk --update --no-cache add git icu-dev libxml2-dev

# DOM
RUN docker-php-ext-install dom

# PDO Mysql
RUN docker-php-ext-install pdo_mysql

# Redis
RUN apk add --no-cache pcre-dev $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis

# Intl
RUN docker-php-ext-configure intl --enable-intl \
    && docker-php-ext-install intl

# Library GD
RUN apk --update --no-cache add jpeg-dev libpng-dev \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Imagick
RUN set -ex \
    && apk add --no-cache --virtual .phpize-deps $PHPIZE_DEPS imagemagick-dev libtool \
    && export CFLAGS="$PHP_CFLAGS" CPPFLAGS="$PHP_CPPFLAGS" LDFLAGS="$PHP_LDFLAGS" \
    && pecl install imagick-3.4.3 \
    && docker-php-ext-enable imagick \
    && apk add --no-cache --virtual .imagick-runtime-deps imagemagick \
    && apk del .phpize-deps

# Exif
RUN docker-php-ext-install exif

# OPCache
RUN docker-php-ext-install opcache

# APCu
RUN apk add --update --no-cache --virtual .build-dependencies $PHPIZE_DEPS \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && pecl clear-cache \
    && apk del .build-dependencies

# Cleanup
RUN rm -rf /tmp/* \
    && rm -rf /var/cache/apk/* \
    && rm -rf tmp/*

COPY --from=composer /usr/bin/composer /usr/bin/composer
WORKDIR /var/www
CMD composer install ; wait-for-it database:3306 ; php bin/console doctrine:schema:update --force ; php-fpm

# Create folders
RUN mkdir -p /var/www/public/uploads
RUN mkdir -p /var/www/public/uploads/courses
RUN mkdir -p /var/www/public/uploads/technologies
RUN mkdir -p /var/www/public/uploads/users
RUN chmod -R 777 /var/www/public/uploads

EXPOSE 9000
