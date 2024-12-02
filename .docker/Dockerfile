FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git

WORKDIR /var/www/html

COPY .. .

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer update --prefer-dist --no-interaction

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

RUN echo "xdebug.mode=develop,debug,coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN echo "memory_limit=512M" > /usr/local/etc/php/conf.d/memory-limit.ini

EXPOSE 8000
CMD ["php-fpm"]
