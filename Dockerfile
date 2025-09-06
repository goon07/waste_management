# Stage 1: Frontend build with Node
FROM node:18 AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 2: PHP-FPM + Nginx
FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    unzip git curl libpq-dev nginx supervisor \
    && docker-php-ext-install pdo pdo_pgsql bcmath

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Workdir
WORKDIR /var/www/html

# Copy Laravel app
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Copy frontend build artifacts
COPY --from=frontend /app/public/build ./public/build

# Fix permissions
RUN chown -R www-data:www-data storage bootstrap/cache

# Copy Nginx config
COPY ./docker/nginx.conf /etc/nginx/conf.d/default.conf

# Copy Supervisor config
COPY ./docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Expose port
EXPOSE 8080

# Start Supervisor (which runs nginx + php-fpm)
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
