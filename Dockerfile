FROM php:8.3.0-apache

WORKDIR /var/www/html

# Mod Rewrite
RUN a2enmod rewrite

# Linux Library
RUN apt-get update -y && apt-get install -y \
    bash \
    libicu-dev \
    libmariadb-dev \
    unzip zip \
    zlib1g-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev

# PHP Extensions
RUN docker-php-ext-install gettext intl pdo_mysql gd zip
RUN docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Désactivé pour le moment
RUN docker-php-ext-install gettext intl pdo_mysql gd zip opcache
RUN pecl install apcu && docker-php-ext-enable apcu
RUN pecl install redis && docker-php-ext-enable redis

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Apache config
COPY apache-vhost.conf /etc/apache2/sites-available/000-default.conf

# Composer install
COPY composer.json composer.lock /var/www/html/
COPY . /var/www/html
RUN COMPOSER_MEMORY_LIMIT=-1 composer install

COPY package.json pnpm-lock.yaml /var/www/html/

# Installer Node.js et pnpm
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g pnpm@9 \
    && pnpm install
