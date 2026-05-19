FROM php:8.2-fpm

# System deps
RUN apt-get update && apt-get install -y \
    git unzip libssl-dev pkg-config libicu-dev libzip-dev zip \
    libpng-dev libjpeg-dev libfreetype6-dev \
    nodejs npm

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd intl pdo pdo_mysql zip

# Install MongoDB extension version 1.21.0 (compatible with your project)
RUN pecl install mongodb-1.21.0 \
    && docker-php-ext-enable mongodb

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

# Install PHP deps WITHOUT running Symfony scripts
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Build assets (Vite)
RUN npm install && npm run build

# Symfony cache warmup (now that env vars exist)
RUN php bin/console cache:warmup --env=prod

CMD ["php-fpm"]
