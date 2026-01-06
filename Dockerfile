# Utiliser PHP 8.2
FROM php:8.2-cli

WORKDIR /app

# Dépendances système
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libxml2-dev libzip-dev zip libpng-dev libonig-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip opcache

# Copier le projet
COPY . .

# Installer Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer

# Installer les dépendances
RUN composer install --no-dev --optimize-autoloader

# Exposer le port attendu par Render (par ex 10000)
EXPOSE 10000

# Lancer Symfony en mode prod
CMD ["php", "-S", "0.0.0.0:10000", "-t", "public"]
