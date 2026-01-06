FROM php:8.2-cli

ARG COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update && apt-get install -y \
    git unzip zip libicu-dev libonig-dev libxml2-dev libzip-dev \
    libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-install intl pdo_mysql mbstring xml zip opcache gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

# Installer les dépendances prod uniquement
RUN composer install --no-dev --optimize-autoloader --prefer-dist --no-scripts

# Générer le cache prod
RUN php bin/console cache:clear --env=prod --no-warmup \
    && php bin/console cache:warmup --env=prod

# Exposer le port HTTP
EXPOSE 8000

# Lancer le serveur PHP intégré sur Render
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
