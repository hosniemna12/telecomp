# Build stage for Node dependencies
FROM node:20-alpine AS node-builder

WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm ci

# Copy JavaScript files for build
COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources/ ./resources/
COPY public/ ./public/

# Build frontend assets
RUN npm run build

# PHP stage
FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    supervisor \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_pgsql \
    mbstring \
    xml \
    zip \
    bcmath \
    gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy application files
COPY . .

# Copy built assets from Node stage
COPY --from=node-builder /app/public/build ./public/build

# Install PHP dependencies
RUN composer install --optimize-autoloader --no-dev

# Set permissions
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

# Copy supervisor configuration
COPY docker/supervisor/laravel.conf /etc/supervisor/conf.d/laravel.conf

# Create required directories
RUN mkdir -p /app/storage/logs

EXPOSE 9000

CMD ["php-fpm"]
