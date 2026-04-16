#!/bin/bash
set -e

echo "🚀 Memulai proses deploy dev..."

echo "📥 Mengambil perubahan terbaru dari Git..."
git restore composer.lock
git pull origin main

echo "📦 Memperbarui dependensi Composer..."
composer update --no-interaction --prefer-dist --optimize-autoloader

nvm use 20
npm install
npm run build

echo "🧹 Membersihkan dan memperbarui cache Laravel..."
php artisan optimize:clear
php artisan optimize
php artisan view:cache
php artisan config:cache
php artisan event:cache
php artisan view:clear

echo "⚡ Menjalankan Dump Autoload..."
composer dump-autoload -o

echo "✅ Deploy selesai dengan sukses!"
