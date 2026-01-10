FROM php:8.2-cli

ARG COMPOSER_ALLOW_SUPERUSER=1

ENV APP_ENV=prod
ENV APP_DEBUG=0

# Installer dépendances système + extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    git unzip zip libicu-dev libonig-dev libxml2-dev libzip-dev \
    libpng-dev libjpeg-dev libfreetype6-dev libpq-dev \
    && docker-php-ext-install intl pdo pdo_pgsql mbstring xml zip opcache gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

# Supprimer configs dev
RUN rm -rf config/packages/dev

# Supprimer cache et vendor pour partir propre
RUN rm -rf var/cache/* vendor/*

# Installer dépendances prod
RUN composer install --no-dev --optimize-autoloader --prefer-dist --no-scripts

# Préparer cache prod
RUN php bin/console cache:clear --env=prod --no-warmup \
 && php bin/console cache:warmup --env=prod

EXPOSE 8000

# Lancer le serveur PHP
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
