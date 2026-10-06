#!/bin/sh
set -e

# Config is cached at start (not build) because env vars like APP_KEY and DB_* arrive at runtime.
# route:cache is skipped on purpose: routes/web.php still has a closure route.
php artisan config:cache
php artisan view:cache
chown -R www-data:www-data storage bootstrap/cache

exec "$@"
