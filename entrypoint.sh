#!/bin/sh
set -e

echo "⏳ Waiting for database..."

# Extraire les infos depuis DATABASE_URL
DB_HOST=$(echo $DATABASE_URL | sed -E 's#.*@([^:/]+).*#\1#')
DB_PORT=$(echo $DATABASE_URL | sed -E 's#.*:([0-9]+)/.*#\1#')
DB_USER=$(echo $DATABASE_URL | sed -E 's#postgresql://([^:@]+).*#\1#')
DB_PASSWORD=$(echo $DATABASE_URL | sed -E 's#postgresql://[^:@]+:([^@]+)@.*#\1#')

# Attendre que PostgreSQL Render soit prêt
echo "⏳ Checking PostgreSQL at $DB_HOST:$DB_PORT..."
while ! pg_isready -h $DB_HOST -p $DB_PORT -U $DB_USER >/dev/null 2>&1; do
  sleep 1
done

echo "✅ Database ready"

# Clear & warmup cache Symfony
php bin/console cache:clear --env=prod --no-warmup
php bin/console cache:warmup --env=prod

# Exécuter migrations et fixtures
php bin/console doctrine:migrations:migrate --no-interaction --env=prod
php bin/console doctrine:fixtures:load --no-interaction --env=prod --append

# Lancer le serveur Symfony
echo "🚀 Starting Symfony server on port 8000"
php -S 0.0.0.0:8000 -t public
