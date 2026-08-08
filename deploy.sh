#!/usr/bin/env bash
#
# Deploy FSM ke VPS (aaPanel) — sekali jalan:
#   git pull → clear cache → migrate → restart queue worker → reload PHP-FPM
#
# Cara pakai di VPS:
#   chmod +x deploy.sh
#   ./deploy.sh
#
set -euo pipefail

APP_DIR="/www/wwwroot/fsm.indomotorlestari.com"

if [ ! -d "$APP_DIR" ]; then
    echo "Folder aplikasi tidak ditemukan: $APP_DIR"
    exit 1
fi

cd "$APP_DIR"

echo "==> [1/6] git pull"
git pull

echo "==> [2/6] clear config & view cache"
php artisan config:clear
php artisan view:clear

echo "==> [3/6] migrate"
php artisan migrate --force

echo "==> [4/6] hentikan queue worker lama"
pkill -f "artisan queue:work" || true
sleep 1

echo "==> [5/6] jalankan ulang queue worker"
mkdir -p storage/logs
nohup php artisan queue:work redis --queue=notifications,default,tracking --tries=3 --timeout=60 \
    >> storage/logs/queue.log 2>&1 &
disown

echo "==> [6/6] reload PHP-FPM (aaPanel)"
if [ -f /etc/init.d/php-fpm-83 ]; then
    /etc/init.d/php-fpm-83 reload
else
    echo "    (script php-fpm-83 tidak ditemukan — reload manual via aaPanel kalau perlu)"
fi

echo ""
echo "==> Selesai. Verifikasi:"
echo "    ps aux | grep queue:work"
echo "    tail -20 storage/logs/queue.log"
