# Stage 1: Build assets
FROM node:18 AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 2: PHP + Composer
FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    unzip git curl libpq-dev nginx \
    && docker-php-ext-install pdo_pgsql

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www/html

# Copy PHP dependencies first (cache layer)
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader

# Copy application code
COPY . .

# Copy built assets from frontend stage
COPY --from=frontend /app/public/build ./public/build

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Copy Nginx config
COPY ./docker/nginx.conf /etc/nginx/conf.d/default.conf

# Expose port
EXPOSE 8080

# Start PHP-FPM + Nginx
CMD service nginx start && php-fpm
