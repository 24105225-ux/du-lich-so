FROM php:8.3-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libzip-dev libonig-dev libxml2-dev libcurl4-openssl-dev \
    && docker-php-ext-install pdo_mysql mbstring zip dom curl \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2.10 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/backend

COPY backend/composer.json backend/composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-dev --optimize-autoloader
COPY backend ./

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
COPY docker/backend/entrypoint.sh /usr/local/bin/dt17-backend
RUN chmod +x /usr/local/bin/dt17-backend

EXPOSE 8000
ENTRYPOINT ["dt17-backend"]
