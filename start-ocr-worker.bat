@echo off
cd /d "%~dp0.."
echo Starting OCR queue worker...
php artisan queue:work database --queue=ocr,default --tries=3 --timeout=600 --sleep=3
