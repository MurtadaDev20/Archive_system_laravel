@echo off
chcp 65001 >nul
cd /d "%~dp0"

echo ========================================
echo   Laravel WebSockets (PHP - منفذ 6001)
echo ========================================
echo.
echo تشغيل: php artisan websockets:serve
echo.
php artisan websockets:serve
