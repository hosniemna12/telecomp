#!/bin/bash
set -e

# Wait for database to be ready
echo "Waiting for database..."
until PGPASSWORD=$DB_PASSWORD psql -h "$DB_HOST" -U "$DB_USERNAME" -d "$DB_DATABASE" -c '\q' 2>/dev/null; do
  echo "Database is unavailable - sleeping"
  sleep 1
done
echo "Database is up!"

# Run migrations if needed
echo "Running migrations..."
php /app/artisan migrate --force

# Clear caches
echo "Clearing caches..."
php /app/artisan config:cache
php /app/artisan route:cache
php /app/artisan view:cache

# Start PHP-FPM
echo "Starting PHP-FPM..."
php-fpm
