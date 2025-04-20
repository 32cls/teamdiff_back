#!/bin/sh

# Wait for Postgres to be ready (adjust host and port as needed)
/wait-for teamdiff_db:5432 --timeout=60 -- echo "Database is up"

# Move to app directory
cd /var/www/html

# Run migrations
php artisan migrate --force

# Start PHP-FPM
exec php-fpm
