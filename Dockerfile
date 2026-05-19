FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git unzip libssl-dev pkg-config libicu-dev libzip-dev zip

RUN docker-php-ext-install intl pdo pdo_mysql zip

RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN php bin/console cache:warmup --env=prod

CMD ["php-fpm"]
