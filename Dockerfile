FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libzip-dev libonig-dev libxml2-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip

RUN pecl install mongodb-1.21.0 \
    && docker-php-ext-enable mongodb

RUN a2enmod rewrite

COPY . /var/www/html/
WORKDIR /var/www/html/

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-scripts

ENV APP_ENV=prod
ENV APP_DEBUG=0

# 👉 Correction Apache
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/apache2.conf

EXPOSE 80

CMD ["apache2-foreground"]
