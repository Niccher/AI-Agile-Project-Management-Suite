# syntax=docker/dockerfile:1
FROM php:8.3-fpm

# ── System dependencies & pre-compiled PHP extensions ─────────────────────────
RUN apt-get update && apt-get install -y --no-install-recommends \
        nginx \
        zip \
        unzip \
    && rm -rf /var/lib/apt/lists/*

# Install pre-compiled PHP extensions (eliminates 10-minute ICU/gd source compilation)
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions intl mysqli pdo_mysql zip gd

# Configure Nginx
COPY nginx.conf /etc/nginx/sites-available/default

WORKDIR /var/www/html

# ── Composer dependency layer ─────────────────────────────────────────────────
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --ignore-platform-reqs

# ── Application code ──────────────────────────────────────────────────────────
COPY . /var/www/html

RUN mkdir -p /var/www/html/writable \
    && chown -R www-data:www-data /var/www/html/writable /var/www/html/public \
    && chmod -R 775 /var/www/html/writable

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
EXPOSE 80
