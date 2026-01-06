# Utiliser l'image PHP officielle avec Apache ou CLI
FROM php:8.2-fpm

# Arguments pour composer
ARG COMPOSER_ALLOW_SUPERUSER=1

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-install intl pdo_mysql mbstring xml zip opcache gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /app

# Copier les fichiers composer
COPY composer.json composer.lock ./

# Installer les dépendances sans dev pour la prod
RUN composer install --no-dev --optimize-autoloader --prefer-dist

# Copier le reste de l'application
COPY . .

# Configurer les permissions pour Symfony (var et cache)
RUN mkdir -p var/cache var/log var/sessions \
    && chown -R www-data:www-data var \
    && chmod -R 775 var

# Construire le cache Symfony pour la production
RUN php bin/console cache:clear --env=prod --no-warmup \
    && php bin/console cache:warmup --env=prod

# Exposer le port pour le serveur web (à adapter si Apache/Nginx externe)
EXPOSE 9000

# Commande par défaut pour FPM
CMD ["php-fpm"]
