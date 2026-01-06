# Utiliser PHP 8.2 CLI
FROM php:8.2-cli

# Arguments pour composer
ARG COMPOSER_ALLOW_SUPERUSER=1

# Installer dépendances système et extensions PHP
RUN apt-get update && apt-get install -y \
    git unzip zip libicu-dev libonig-dev libxml2-dev libzip-dev \
    libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-install intl pdo_mysql mbstring xml zip opcache gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /app

# Copier tout le code
COPY . .

# Installer les dépendances prod sans scripts automatiques
RUN composer install --no-dev --optimize-autoloader --prefer-dist --no-scripts

# Générer le cache Symfony pour la production
RUN php bin/console cache:clear --env=prod --no-warmup \
    && php bin/console cache:warmup --env=prod

# Exposer le port HTTP attendu par Render
EXPOSE 8000

# Commande pour lancer le serveur PHP intégré sur le port 8000
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
