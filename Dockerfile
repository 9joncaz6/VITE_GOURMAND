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

# Copier uniquement composer.json pour optimiser le cache Docker
COPY composer.json composer.lock ./

# Installer les dépendances sans scripts (Symfony les exécutera ensuite)
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copier le reste du projet
COPY . .

ENV APP_ENV=prod
ENV APP_DEBUG=0

#  IMPORTANT : reconstruire le cache prod pour prendre en compte les putenv()
RUN php bin/console cache:clear --env=prod

EXPOSE 80

CMD ["php", "-S", "0.0.0.0:80", "-t", "public"]
