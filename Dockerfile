# ============================
# Symfony + PostgreSQL pour Render
# ============================

FROM php:8.2-cli

# Installer dépendances système + PostgreSQL
RUN apt-get update && apt-get install -y \
    git unzip zip libicu-dev libonig-dev libxml2-dev libzip-dev \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libpq-dev postgresql-client \
    && docker-php-ext-install intl pdo_pgsql pgsql mbstring xml zip opcache gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Définir le dossier de travail
WORKDIR /app

# Copier le projet
COPY . .

# Supprimer cache et vendor (build propre)
RUN rm -rf var/cache/* vendor/*

# Installer les dépendances Symfony (prod seulement)
RUN composer install --no-dev --optimize-autoloader --prefer-dist --no-scripts

# Copier le script d'entrypoint
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Exposer le port 8000 pour Render
EXPOSE 8000

# Lancer le script au démarrage
CMD ["/entrypoint.sh"]
