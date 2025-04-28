FROM composer:lts AS vendor

WORKDIR /tmp

COPY composer.json composer.lock ./

RUN composer install --no-interaction --no-autoloader --no-scripts

COPY . .

RUN composer dump-autoload --optimize


FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    netcat-openbsd \
    libpq-dev \
    libzip-dev \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring zip gd

RUN curl -o /wait-for https://raw.githubusercontent.com/eficode/wait-for/v2.2.4/wait-for && chmod +x /wait-for

WORKDIR /var/www/html

# Fix permissions: make Laravel storage & cache writable for www-data
RUN mkdir -p /.config/psysh && chown -R 1000:1000 /var/www/html /.config/psysh

USER 1000:1000

COPY --chown=1000:1000 --from=vendor /tmp/vendor ./vendor
COPY --chown=1000:1000 . .

#COPY docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

#RUN chmod +x /usr/local/bin/docker-entrypoint.sh

#ENTRYPOINT ["docker-entrypoint.sh"]
