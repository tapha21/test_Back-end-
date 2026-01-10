#!/bin/sh
set -e

echo "⏳ Waiting for database..."

# EXTRAIT LES INFOS DE DATABASE_URL
# Ex: postgresql://user:password@host:5432/dbname?serverVersion=16
DB_URL_CLEAN=$(echo $DATABASE_URL | sed -E 's/\?.*//')
DB_HOST=$(echo $DB_URL_CLEAN | sed -E 's#postgresql://[^:]+:[^@]+@([^:/]+).*#\1#')
DB_PORT=$(echo $DB_URL_CLEAN | sed -E 's#postgresql://[^:]+:[^@]+@[^:/]+:([0-9]+).*#\1#')
DB_USER=$(echo $DB_URL_CLEAN | sed -E 's#postgresql://([^:]+):.*@.*#\1#')
DB_PASSWORD=$(echo $DB_URL_CLEAN | sed -E 's#postgresql://[^:]+:([^@]+)@.*#\1#')

# ATTENTE QUE LA DB SOIT PRÊTE
echo "⏳ Checking PostgreSQL at $DB_HOST:$DB_PORT..."
export PGPASSWORD=$DB_PASSWORD
while ! pg_isready -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" >/dev/null 2>&1; do
  sleep 1
done
echo "✅ Database ready"

# CLEAR & WARMUP CACHE Symfony
php bin/console cache:clear --env=prod --no-warmup
php bin/console cache:warmup --env=prod

# MIGRATIONS + FIXTURES
php bin/console doctrine:migrations:migrate --no-interaction --env=prod
php bin/console doctrine:fixtures:load --no-interaction --env=prod --append

# LANCER LE SERVEUR Symfony sur le port Render
echo "🚀 Starting Symfony server on port $PORT"
php -S 0.0.0.0:$PORT -t public
