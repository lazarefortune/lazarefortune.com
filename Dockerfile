FROM php:8.1-fpm-alpine

RUN apk add --no-cache \
    curl \
    icu-dev \
    libzip-dev \
    unzip \
    git \
    mariadb-client \
    nodejs \
    npm \
    bash

# Installer les extensions PHP nécessaires
RUN docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) \
    intl \
    opcache \
    pdo \
    pdo_mysql \
    zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/symfony

# Use the ADD directive to copy the local project files to the container
ADD . /var/www/symfony

# Allow Composer to run as super user
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN composer install
RUN npm install

CMD ["sh", "-c", "npm run build && php -S 0.0.0.0:8000 -t public"]
