FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git unzip curl wget libonig-dev libzip-dev libpng-dev \
    libicu-dev libxml2-dev libpq-dev postgresql-client \
    && docker-php-ext-install \
    pdo_pgsql pdo_mysql mbstring zip exif pcntl bcmath intl gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN echo "upload_max_filesize = 64M\npost_max_size = 64M" > /usr/local/etc/php/conf.d/uploads.ini

# Composer prefers a clean worktree; keep the local vendor tree out of the image.
ENV COMPOSER_ALLOW_SUPERUSER=1

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install Node.js for asset building
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - && \
    apt-get install -y nodejs && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Install PHP dependencies
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts

# Copy the rest of the application after dependencies are installed.
COPY . /var/www/html

# Let Laravel finish its Composer hooks once the app code is present.
RUN composer dump-autoload --optimize && php artisan package:discover --ansi

# Install and build frontend assets
RUN npm ci && npm run build

# Create storage/framework subdirectories and set permissions
RUN mkdir -p /var/www/html/storage/framework/views && \
    mkdir -p /var/www/html/storage/framework/cache && \
    mkdir -p /var/www/html/storage/framework/sessions && \
    mkdir -p /var/www/html/storage/logs && \
    mkdir -p /var/www/html/storage/app/public && \
    mkdir -p /var/www/html/bootstrap/cache && \
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=40s --retries=3 \
    CMD curl -f http://localhost:8000/ || exit 1

EXPOSE 8000

CMD ["sh", "-c", "mkdir -p storage/framework/views storage/framework/cache storage/framework/sessions storage/logs storage/app/public && php artisan migrate --force && php artisan optimize:clear && php artisan storage:link --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]
