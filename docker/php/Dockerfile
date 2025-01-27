# Use PHP 8.1 FPM Alpine as the base image (lightweight and fast)
FROM php:8.1-fpm-alpine

# Install required packages and dependencies
RUN apk update && apk add --no-cache \
    bash \
    curl \
    git \
    unzip \
    libpng-dev \
    libzip-dev \
    zlib-dev \
    oniguruma-dev \
    autoconf \
    gcc \
    g++ \
    make \
    libxml2-dev && \
    # Install PHP extensions (zip, gd, pdo, pdo_mysql, mbstring, opcache, xml)
    docker-php-ext-configure zip && \
    docker-php-ext-install zip gd pdo pdo_mysql mbstring opcache xml && \
    # Enable Opcache
    docker-php-ext-enable opcache

    
# Install Composer globally
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory in the container to /app
WORKDIR /var/www/html

# Expose port for PHP-FPM (default is 9000)
EXPOSE 9000

