#!/bin/sh
set -e
cd /var/www/html

if [ -z "$APP_KEY" ]; then
  echo "audiodec: ERROR — APP_KEY is not set (run: php artisan key:generate --show)" >&2
  exit 1
fi
if [ -z "$MONGODB_URI" ]; then
  echo "audiodec: ERROR — MONGODB_URI is not set" >&2
  exit 1
fi

export DB_CONNECTION="${DB_CONNECTION:-mongodb}"
export MONGODB_DATABASE="${MONGODB_DATABASE:-audiodec}"

export LOG_CHANNEL="${LOG_CHANNEL:-stderr}"

php artisan optimize:clear 2>/dev/null || true

PORT="${PORT:-10000}"
exec php artisan serve --host=0.0.0.0 --port="$PORT"
