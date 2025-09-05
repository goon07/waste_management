# Use official PHP CLI image
FROM php:8.2-cli

# Set working directory
WORKDIR /app

# Install system dependencies
RUN apt-get update && apt-get install -y \
    unzip git curl libpq-dev nodejs npm \
    && docker-php-ext-install pdo_pgsql

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy composer files and install PHP dependencies
COPY composer.json composer.lock ./
RUN composer install --optimize-autoloader --no-dev

# Copy all project files
COPY . .

# Build Node/Vite assets
RUN npm install
RUN npm run build

# Cache Laravel config, routes, views
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

# Expose port for Railway
EXPOSE 8080

# Run Laravel
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
