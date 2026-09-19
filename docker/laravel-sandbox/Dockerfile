FROM composer:2 AS composer

FROM php:8.3-cli-bookworm

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        bash \
        ca-certificates \
        curl \
        git \
        libicu-dev \
        libonig-dev \
        libxml2-dev \
        libzip-dev \
        nodejs \
        npm \
        unzip \
        zip \
    && docker-php-ext-install \
        bcmath \
        intl \
        mbstring \
        pcntl \
        pdo_sqlite \
        xml \
        zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

RUN composer create-project laravel/laravel:^12.0 /opt/devroad-template \
        --no-interaction \
        --prefer-dist \
    && cd /opt/devroad-template \
    && php artisan key:generate --force \
    && mkdir -p database \
    && touch database/database.sqlite \
    && php artisan migrate --graceful --force \
    && npm install \
    && npm run build \
    && rm -rf storage/logs/*.log

COPY entrypoint.sh /usr/local/bin/devroad-entrypoint
RUN chmod +x /usr/local/bin/devroad-entrypoint

WORKDIR /workspace

ENTRYPOINT ["/usr/local/bin/devroad-entrypoint"]
