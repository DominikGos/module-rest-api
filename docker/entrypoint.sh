#!/bin/bash

echo "Setting permissions for storage and bootstrap/cache directories..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

if [ ! -f "vendor/autoload.php" ]; then
    composer install --no-progress --no-interaction 
fi

if [ ! -f ".env" ]; then 
    echo "Missing .env file"
fi

php artisan key:generate
php artisan migrate
php artisan cache:clear
php artisan config:clear
php artisan route:clear

exec php-fpm