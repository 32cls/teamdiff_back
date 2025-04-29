FROM php:8.4-fpm-alpine3.21

RUN apk add --no-cache \
    bash \
    nodejs \
    npm \
    php84-ctype \
    php84-curl \
    php84-dom \
    php84-fileinfo \
    php84-fpm \
    php84-gd \
    php84-intl \
    php84-mbstring \
    php84-opcache \
    php84-openssl \
#    php84-pdo \
#    php84-pdo_pgsql \
#    php84-pgsql \
    php84-phar \
    php84-session \
    php84-tokenizer \
    php84-xml \
    php84-xmlreader \
    php84-xmlwriter \
    postgresql-dev \
    && docker-php-ext-install pdo pdo_pgsql

COPY docker/local/php-fpm.conf /etc/php84/php-fpm.d/default.conf

#RUN curl -o /wait-for https://raw.githubusercontent.com/eficode/wait-for/v2.2.4/wait-for && chmod +x /wait-for

WORKDIR /var/www/html

COPY composer.json composer.lock ./

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN composer install --no-interaction --no-autoloader --no-scripts

COPY . .

RUN composer dump-autoload --optimize

USER 1000:1000

ENV PATH="./vendor/bin:${PATH}"

#COPY docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

#RUN chmod +x /usr/local/bin/docker-entrypoint.sh
