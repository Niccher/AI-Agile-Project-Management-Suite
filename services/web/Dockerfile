# syntax=docker/dockerfile:1
FROM php:8.4.27-apache

# ── System dependencies & tools ───────────────────────────────────────────────
RUN apt-get update && apt-get install -y --no-install-recommends \
        zip \
        unzip \
    && rm -rf /var/lib/apt/lists/*

# ── Apache Configuration ──────────────────────────────────────────────────────
# Enable mod_rewrite, mod_headers, and mod_deflate for CodeIgniter 4 routing, CORS, and compression
RUN a2enmod rewrite headers deflate

# Point Apache DocumentRoot to CodeIgniter's /public directory
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Configure directory permissions, allow .htaccess overrides, and apply security hardening
RUN printf '<Directory /var/www/html/public>\n\
    Options -Indexes +FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>\n\
ServerTokens Prod\n\
ServerSignature Off\n\
TraceEnable Off\n' > /etc/apache2/conf-available/codeigniter.conf \
    && a2enconf codeigniter

# ── Pre-compiled PHP extensions ───────────────────────────────────────────────
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions intl mysqli pdo_mysql zip gd redis

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
