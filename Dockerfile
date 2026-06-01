FROM php:8.3-fpm

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpq-dev \
    libgd-dev \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql pdo_mysql gd zip

# Install Redis PHP extension
RUN pecl install redis && docker-php-ext-enable redis
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy composer files first
COPY composer.json composer.lock ./

# Install dependencies before copying project to leverage Docker cache
RUN composer install --no-interaction --no-dev --prefer-dist --no-scripts

# Copy project files
COPY . .

# Run composer scripts after copying artisan
RUN composer run-script post-autoload-dump

# Set permissions more efficiently (only for app directory)
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
