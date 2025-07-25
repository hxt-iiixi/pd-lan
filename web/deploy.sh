#!/bin/bash
set -e

echo "📦 Installing dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader

echo "🔧 Optimizing Laravel..."
php artisan config:clear
php artisan config:cache
php artisan route:cache

echo "🧱 Running migrations without reset..."
php artisan migrate --force
