FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git unzip libssl-dev pkg-config libicu-dev libzip-dev zip \
    libpng-dev libjpeg-dev libfreetype6-dev

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd intl pdo pdo_mysql zip

RUN pecl install mongodb-1.21.0 \
    && docker-php-ext-enable mongodb

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-scripts

RUN php bin/console cache:warmup --env=prod

CMD ["php-fpm"]
