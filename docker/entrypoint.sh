#!/bin/bash

# Set proper ownership and permissions for storage and cache
echo "Setting permissions for storage and bootstrap/cache directories..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

if [ ! -f "vendor/autoload.php" ]; then
    composer install --no-progress --no-interaction 
fi

if [ ! -f ".env" ]; then 
    echo "Creating env file for env $APP_ENV"
    cp .end.example .env 
else 
    echo "env file exists."
fi

php artisan key:generate

exec php-fpm