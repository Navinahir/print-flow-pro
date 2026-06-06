#!/usr/bin/env sh
# Writes Laravel .env on the VPS from environment variables (GitHub Actions secrets).
# Never commit .env to git — this script is the recovery path when .env is missing.
set -eu

APP_ROOT="${DEPLOY_PATH:-/var/www/xycubic}"

if [ ! -d "$APP_ROOT" ]; then
    echo "ERROR: DEPLOY_PATH does not exist: $APP_ROOT" >&2
    exit 1
fi

cd "$APP_ROOT"

if [ -z "${APP_KEY:-}" ]; then
    echo "ERROR: APP_KEY is not set. Add GitHub Actions secret APP_KEY (php artisan key:generate --show)." >&2
    exit 1
fi

APP_ENV="${APP_ENV:-production}"
APP_DEBUG="${APP_DEBUG:-false}"
APP_URL="${APP_URL:-https://tw.xycubic.com}"
APP_NAME="${APP_NAME:-XY Cubic Shopee}"

DB_CONNECTION="${DB_CONNECTION:-mysql}"
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:-}"
DB_USERNAME="${DB_USERNAME:-}"
DB_PASSWORD="${DB_PASSWORD:-}"

if [ -z "$DB_DATABASE" ] || [ -z "$DB_USERNAME" ]; then
    echo "ERROR: DB_DATABASE and DB_USERNAME secrets are required." >&2
    exit 1
fi

umask 077

cat > .env << EOF
APP_NAME="${APP_NAME}"
APP_ENV=${APP_ENV}
APP_KEY=${APP_KEY}
APP_DEBUG=${APP_DEBUG}
APP_URL=${APP_URL}

DOMAIN_ROUTING_ENABLED=true
DOMAIN_PORT_ROUTING=false

APP_TIMEZONE=Asia/Singapore
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=${DB_CONNECTION}
DB_HOST=${DB_HOST}
DB_PORT=${DB_PORT}
DB_DATABASE=${DB_DATABASE}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync

CACHE_STORE=file

REDIS_CLIENT=predis
REDIS_HOST=${REDIS_HOST:-127.0.0.1}
REDIS_PASSWORD=${REDIS_PASSWORD:-null}
REDIS_PORT=${REDIS_PORT:-6379}

MAIL_MAILER=log
MAIL_FROM_ADDRESS="noreply@xycubic.com"
MAIL_FROM_NAME="\${APP_NAME}"

VITE_APP_NAME="\${APP_NAME}"

# M2+ — uncomment after Supervisor queue workers are configured on the VPS
# QUEUE_CONNECTION=redis
# CACHE_STORE=redis
# BROWSERSHOT_NODE_BINARY=/usr/bin/node
# BROWSERSHOT_NPM_BINARY=/usr/bin/npm
# BROWSERSHOT_CHROME_PATH=/usr/bin/google-chrome
EOF

chmod 600 .env
echo "Wrote .env at ${APP_ROOT}/.env ($(wc -c < .env | tr -d ' ') bytes)"

if command -v php >/dev/null 2>&1 && [ -f artisan ]; then
    php artisan config:clear 2>/dev/null || true
fi
