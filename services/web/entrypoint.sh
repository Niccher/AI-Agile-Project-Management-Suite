#!/bin/bash
set -e

# Wait for MySQL to be fully ready (beyond just ping)
echo "Waiting for MySQL to accept connections..."
max_retries=30
count=0

export DB_HOST=${MYSQLHOST:-mysql}
export DB_PORT=${MYSQLPORT:-3306}
export DB_USER=${MYSQLUSER:-root}
export DB_PASS=${MYSQLPASSWORD:-root_password}

while ! php -r "new PDO('mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT'), getenv('DB_USER'), getenv('DB_PASS'));" 2>/dev/null; do
    count=$((count + 1))
    if [ $count -ge $max_retries ]; then
        echo "ERROR: MySQL did not become ready in time. Starting PHP-FPM and Nginx without migrations."
        php-fpm -D
        exec nginx -g "daemon off;"
    fi
    echo "MySQL not ready yet... retrying ($count/$max_retries)"
    sleep 2
done

echo "MySQL is ready!"

# Run migrations (safe to run multiple times — only applies pending migrations)
echo "Running database migrations..."
cd /var/www/html
php spark migrate --all 2>&1 || echo "WARNING: Migration encountered an issue. Check logs."

# Run DemoSeeder (creates default admin account and demo projects safely)
echo "Running database seeders..."
php spark db:seed DemoSeeder 2>&1 || echo "NOTE: Seed data already exists or encountered an issue."

echo "Setting up writable directories..."
cd /var/www/html
mkdir -p writable/uploads/avatars
mkdir -p writable/reports
chown -R www-data:www-data writable/
chmod -R 775 writable/

echo "Migrations complete. Starting PHP-FPM and Nginx..."
php-fpm -D
exec nginx -g "daemon off;"
