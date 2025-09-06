# ----------------------------
# Stage 1: Frontend build
# ----------------------------
FROM node:18 as frontend
WORKDIR /app

COPY package*.json ./
RUN npm install

COPY . .
RUN npm run build

# ----------------------------
# Stage 2: PHP + Nginx
# ----------------------------
FROM php:8.2-fpm

# Install system dependencies + supervisor
RUN apt-get update && apt-get install -y \
    unzip git curl libpq-dev nginx supervisor \
    && docker-php-ext-install pdo_pgsql

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

# Copy composer files and install dependencies without scripts
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy full Laravel app
COPY . .

# Run post-autoload-dump scripts
RUN composer run-script post-autoload-dump

# Copy frontend build
COPY --from=frontend /app/public/build ./public/build

# Fix permissions
RUN chown -R www-data:www-data storage bootstrap/cache

# Copy nginx and supervisor configs
COPY ./docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY ./docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Expose port
EXPOSE 8080

# Start Supervisor (which will start nginx + php-fpm + Laravel scheduler)
CMD ["supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
