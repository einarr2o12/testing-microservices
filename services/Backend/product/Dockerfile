FROM --platform=linux/amd64 php:8.3-apache

RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libpq-dev \
    curl

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configure Apache
RUN a2enmod rewrite

# Create Apache configuration
RUN echo '<VirtualHost *:80>\n\
    ServerAdmin webmaster@localhost\n\
    DocumentRoot /var/www/html\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
    <Directory /var/www/html>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# Copy composer files first for better caching
COPY composer.json composer.lock* ./

# Install dependencies
RUN composer install --no-scripts --no-autoloader --no-interaction --no-dev

# Copy standalone health check file (for Kubernetes probes)
COPY health.php /var/www/html/health

# Copy application code
COPY . .

# Generate optimized autoloader
RUN composer dump-autoload --optimize --no-dev

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html

# Fix entrypoint script permissions and line endings
RUN sed -i 's/\r$//' /usr/local/bin/docker-php-entrypoint && \
    chmod +x /usr/local/bin/docker-php-entrypoint

EXPOSE 80

ENTRYPOINT ["/bin/bash", "-c"]
CMD ["apache2-foreground"]