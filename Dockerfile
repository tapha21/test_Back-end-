# Utilise PHP 8.2 avec les extensions nécessaires pour Symfony
FROM php:8.2-cli

# Définir le répertoire de travail
WORKDIR /app

# Installer les dépendances système nécessaires pour Symfony et Composer
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    && docker-php-ext-install intl pdo pdo_mysql zip opcache

# Copier le code source du projet
COPY . .

# Installer Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer

# Installer les dépendances PHP/Symfony en production
RUN composer install --no-dev --optimize-autoloader

# Exposer le port sur lequel le serveur va tourner (Render free plan)
EXPOSE 10000

# Commande pour lancer le serveur Symfony
CMD ["php", "-S", "0.0.0.0:10000", "-t", "public"]
