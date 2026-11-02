#!/usr/bin/env bash
# CDS World: update the app on the cPanel server. Run from anywhere: bash deploy/deploy.sh
# Touches ONLY this app's directory. Uses explicit PHP 8.4 so other sites' PHP versions are unaffected.
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PHP="${PHP_BIN:-/opt/cpanel/ea-php84/root/usr/bin/php}"
COMPOSER="${COMPOSER_BIN:-/usr/local/bin/composer}"
BRANCH="${BRANCH:-main}"

cd "$APP_DIR"
[ -f .env ] || { echo "Missing .env (copy deploy/.env.production.example first)"; exit 1; }

echo "==> Pull $BRANCH"
git fetch origin "$BRANCH"
git merge --ff-only "origin/$BRANCH"

echo "==> Maintenance mode on"
"$PHP" artisan down --retry=60 || true
trap '"$PHP" artisan up || true' EXIT

echo "==> Composer (no dev)"
COMPOSER_MEMORY_LIMIT=-1 "$PHP" "$COMPOSER" install --no-dev --optimize-autoloader --no-interaction

echo "==> Migrate"
"$PHP" artisan migrate --force

echo "==> Storage link + caches"
"$PHP" artisan storage:link || true
"$PHP" artisan config:cache
"$PHP" artisan route:cache
"$PHP" artisan view:cache
"$PHP" artisan event:cache
"$PHP" artisan filament:cache-components || true
"$PHP" artisan icons:cache || true
"$PHP" artisan queue:restart || true

echo "==> Done"
