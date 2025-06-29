# Multi-stage build for smaller image size
FROM php:8.4-fpm-alpine AS base

# Install system dependencies (Alpine is much smaller than Debian)
RUN apk add --no-cache \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    postgresql-dev \
    zip \
    unzip \
    git

# Install PHP extensions
RUN docker-php-ext-install pdo_pgsql pgsql mbstring exif pcntl bcmath gd

# Dependencies stage
FROM base AS dependencies
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Install dependencies (production only, optimized)
RUN composer install --no-dev --optimize-autoloader --no-scripts --prefer-dist

# Production stage
FROM base AS production
WORKDIR /var/www/html

# Copy vendor from dependencies stage
COPY --from=dependencies /var/www/html/vendor ./vendor

# Copy application files
COPY . .

# Set permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html/storage && \
    chmod -R 755 /var/www/html/bootstrap/cache

# Run Laravel setup
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]