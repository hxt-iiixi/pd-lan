#!/bin/bash
set -e

echo "📦 Installing dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader

echo "🔧 Optimizing Laravel..."
php artisan config:clear
php artisan config:cache

echo "🧱 Resetting and seeding database..."
php artisan migrate:fresh --seed --force

echo "🚀 Starting Laravel server..."
php artisan serve --host=0.0.0.0 --port=8080
