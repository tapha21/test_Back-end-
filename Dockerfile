# ============================
# Dockerfile Symfony + PostgreSQL
# ============================

FROM php:8.2-cli

# Installer dépendances système + PostgreSQL
RUN apt-get update && apt-get install -y \
    git unzip zip libicu-dev libonig-dev libxml2-dev libzip-dev \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libpq-dev \
    && docker-php-ext-install intl pdo_pgsql pgsql mbstring xml zip opcache gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Définir le dossier de travail
WORKDIR /app

# Copier le projet
COPY . .

# Supprimer cache et vendor (propre pour build)
RUN rm -rf var/cache/* vendor/*

# Installer les dépendances Symfony
RUN composer install --no-dev --optimize-autoloader --prefer-dist --no-scripts

# Clear & warmup cache prod
RUN php bin/console cache:clear --env=prod --no-warmup \
 && php bin/console cache:warmup --env=prod

# 🔥 Migrations + Fixtures automatique
RUN php bin/console doctrine:migrations:migrate --no-interaction --env=prod \
 && php bin/console doctrine:fixtures:load --no-interaction --env=prod --append

# Exposer le port
EXPOSE 8000

# Lancer le serveur PHP
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
