@echo off
chcp 65001 >nul
cd /d "%~dp0"

echo ========================================
echo   Soketi - Pusher محلي (منفذ 6001)
echo ========================================

for /f "tokens=*" %%v in ('node -v 2^>nul') do set NODE_VER=%%v
echo Node.js: %NODE_VER%

echo %NODE_VER% | findstr /R "^v1[89]\." >nul
if errorlevel 1 (
    echo.
    echo [تحذير] Soketi يحتاج Node.js 18 أو 20 LTS
    echo         Node 23+ غير مدعوم حالياً على Windows
    echo.
    echo الحلول:
    echo   1. ثبّت nvm-windows ثم: nvm install 18 ^& nvm use 18
    echo   2. أو اعتمد على التحديث التلقائي كل 3 ثوانٍ ^(polling^)
    echo.
)

echo.
echo تشغيل Soketi...
call npm run websocket
