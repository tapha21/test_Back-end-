# -------- Stage 1 : Build --------
FROM php:8.2-cli AS build

# Arguments pour composer
ARG COMPOSER_ALLOW_SUPERUSER=1

# Installer dépendances système et PHP
RUN apt-get update && apt-get install -y \
    git unzip zip libicu-dev libonig-dev libxml2-dev \
    libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-install intl pdo_mysql mbstring xml zip opcache gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /app

# Copier composer et installer les dépendances prod sans scripts automatiques
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --prefer-dist --no-scripts

# Copier tout le code de l'application
COPY . .

# Générer le cache Symfony pour la prod
RUN php bin/console cache:clear --env=prod --no-warmup \
    && php bin/console cache:warmup --env=prod

# -------- Stage 2 : Production --------
FROM php:8.2-fpm

# Installer les extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    libicu-dev libonig-dev libxml2-dev libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-install intl pdo_mysql mbstring xml zip opcache gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Copier l'application depuis le stage build
COPY --from=build /app /app

# Permissions Symfony
RUN mkdir -p var/cache var/log var/sessions \
    && chown -R www-data:www-data var \
    && chmod -R 775 var

# Exposer le port pour PHP-FPM
EXPOSE 9000

# Commande par défaut
CMD ["php-fpm"]
