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

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

COPY docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

RUN git config --global --add safe.directory . && composer install

# Fix permissions: make Laravel storage & cache writable for www-data
RUN mkdir -p /.config/psysh && chown -R www-data:www-data /var/www/html /.config/psysh

RUN chmod +x /usr/local/bin/docker-entrypoint.sh

USER www-data

ENTRYPOINT ["docker-entrypoint.sh"]
