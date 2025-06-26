FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx \
    supervisor

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN addgroup -g 1000 www && \
    adduser -u 1000 -G www -s /bin/sh -D www

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents
COPY . /var/www

# Copy existing application directory permissions
COPY --chown=www:www . /var/www

# Install composer dependencies
RUN composer install --optimize-autoloader --no-dev

# Configure nginx
RUN echo 'server { \
    listen 80; \
    index index.php index.html; \
    error_log  /var/log/nginx/error.log; \
    access_log /var/log/nginx/access.log; \
    root /var/www/public; \
    location ~ \.php$ { \
        try_files $uri =404; \
        fastcgi_split_path_info ^(.+\.php)(/.+)$; \
        fastcgi_pass 127.0.0.1:9000; \
        fastcgi_index index.php; \
        include fastcgi_params; \
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; \
        fastcgi_param PATH_INFO $fastcgi_path_info; \
    } \
    location / { \
        try_files $uri $uri/ /index.php?$query_string; \
        gzip_static on; \
    } \
}' > /etc/nginx/http.d/default.conf

# Configure supervisor
RUN echo '[supervisord] \
nodaemon=true \
[program:php-fpm] \
command=php-fpm \
autostart=true \
autorestart=true \
[program:nginx] \
command=nginx -g "daemon off;" \
autostart=true \
autorestart=true' > /etc/supervisor/conf.d/supervisord.conf

# Set permissions
RUN chown -R www:www /var/www
RUN chmod -R 755 /var/www/storage

# Change current user to www
USER www

# Expose port 80 and start supervisor
EXPOSE 80
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]