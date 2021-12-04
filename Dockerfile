FROM php:7.3-apache

WORKDIR /var/www/html

COPY . .

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && apt update && apt install -y git zip unzip \
    && docker-php-ext-install pdo_mysql \
    && mv .env.example .env \
    && a2enmod rewrite \
    && composer install

ENV DB_HOST="db" REDIS_HOST="redis"