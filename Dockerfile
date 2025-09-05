# Stage 1: Frontend build
FROM node:18 as frontend
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 2: PHP + Nginx
FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    unzip git curl libpq-dev nginx \
    && docker-php-ext-install pdo_pgsql

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

# Copy Laravel app
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Copy frontend build artifacts
COPY --from=frontend /app/public/build ./public/build

# Fix permissions
RUN chown -R www-data:www-data storage bootstrap/cache

# Copy nginx config
COPY ./docker/nginx.conf /etc/nginx/conf.d/default.conf

# Expose port
EXPOSE 8080

# Start PHP-FPM + Nginx
CMD php-fpm -D && nginx -g 'daemon off;'
