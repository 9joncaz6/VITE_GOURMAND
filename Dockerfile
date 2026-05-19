FROM php:8.2

RUN apt-get update && apt-get install -y \
    git unzip libssl-dev pkg-config libicu-dev libzip-dev zip \
    libpng-dev libjpeg-dev libfreetype6-dev

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd intl pdo pdo_mysql zip

RUN pecl install mongodb-1.21.0 \
    && docker-php-ext-enable mongodb

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# 🔥 Définir APP_ENV AVANT toute commande Symfony
ENV APP_ENV=prod
ENV APP_DEBUG=0

# Copier uniquement composer.json pour optimiser le cache
COPY composer.json composer.lock ./

RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copier le reste du projet
COPY . .

# 🔥 Reconstruire le cache prod SANS charger .env
RUN php bin/console cache:clear --env=prod --no-debug

EXPOSE 80

CMD ["php", "-S", "0.0.0.0:80", "-t", "public"]
