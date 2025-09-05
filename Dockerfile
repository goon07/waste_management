# Use official PHP image with CLI + necessary extensions
FROM php:8.2-cli

# Set working directory
WORKDIR /app

# Install system dependencies
RUN apt-get update && apt-get install -y \
    unzip git curl libpq-dev nodejs npm \
    && docker-php-ext-install pdo_pgsql

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy only composer files first (for caching)
COPY composer.json composer.lock ./

# Install PHP dependencies without running scripts yet
RUN composer install --optimize-autoloader --no-dev --no-scripts

# Copy the rest of the project
COPY . .

# Now run Laravel artisan commands (artisan exists now)
RUN php artisan config:clear
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

# Install Node dependencies and build assets
RUN npm install
RUN npm run build

# Expose the port Laravel will run on
EXPOSE 8080

# Command to run Laravel's built-in server
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
