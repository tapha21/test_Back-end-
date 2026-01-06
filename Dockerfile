# Base image
FROM php:8.2-cli

ARG COMPOSER_ALLOW_SUPERUSER=1

# Installer extensions PHP et dépendances système
RUN apt-get update && apt-get install -y \
    git unzip zip libicu-dev libonig-dev libxml2-dev libzip-dev \
    libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-install intl pdo_mysql mbstring xml zip opcache gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Répertoire de travail
WORKDIR /app

# Copier tout le code
COPY . .

# Supprimer cache et vendor pour repartir propre
RUN rm -rf var/cache/* vendor/*

# Installer uniquement prod
RUN composer install --no-dev --optimize-autoloader --prefer-dist --no-scripts

# Générer cache Symfony prod
RUN php bin/console cache:clear --env=prod --no-warmup \
    && php bin/console cache:warmup --env=prod

# Exposer port HTTP pour Render
EXPOSE 8000

# Lancer le serveur PHP intégré
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
