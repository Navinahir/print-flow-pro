#!/usr/bin/env sh
# VPS deploy script — copy to /var/www/xycubic/deploy.sh and run from GitHub Actions.
# Requires: git, composer, npm, php 8.3+
set -eu

APP_ROOT="${DEPLOY_PATH:-/var/www/xycubic}"
BRANCH="${DEPLOY_BRANCH:-main}"

cd "$APP_ROOT"

echo "==> Pull latest (${BRANCH})"
git fetch origin
git checkout "$BRANCH"
git pull origin "$BRANCH"

echo "==> Restore production .env from GitHub secrets"
if [ -f scripts/sync-production-env.sh ]; then
    sh scripts/sync-production-env.sh
fi

echo "==> Install PHP dependencies"
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Build frontend assets"
npm ci
npm run build

echo "==> Run migrations and domain seed"
php artisan migrate --force
php artisan db:seed --class=DomainSettingSeeder --force
php artisan config:clear
php artisan domain:validate --fix-hosts

echo "==> Cache config for production"
php artisan config:cache
php artisan route:cache
php artisan view:cache

# M2+ — uncomment when Redis queue workers are configured
php artisan queue:restart

echo "==> Deploy complete"
