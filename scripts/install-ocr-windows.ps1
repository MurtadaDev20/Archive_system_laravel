# Install Tesseract OCR + Poppler on Windows (Laragon)
# Run in PowerShell as Administrator:
#   Set-ExecutionPolicy -Scope Process Bypass
#   .\scripts\install-ocr-windows.ps1

$ErrorActionPreference = "Stop"

Write-Host "=== Archive System — OCR Dependencies (Windows) ===" -ForegroundColor Cyan

function Test-Command($name) {
    return [bool](Get-Command $name -ErrorAction SilentlyContinue)
}

# 1) Tesseract via winget
if (-not (Test-Command "tesseract")) {
    Write-Host "Installing Tesseract OCR via winget..." -ForegroundColor Yellow
    if (Test-Command "winget") {
        winget install --id UB-Mannheim.TesseractOCR -e --accept-source-agreements --accept-package-agreements
    } else {
        Write-Warning "winget not found. Download Tesseract manually:"
        Write-Host "https://github.com/UB-Mannheim/tesseract/wiki"
    }
} else {
    Write-Host "Tesseract already installed." -ForegroundColor Green
}

# 2) Poppler (pdftotext / pdftoppm) via winget
if (-not (Test-Command "pdftotext")) {
    Write-Host "Installing Poppler via winget..." -ForegroundColor Yellow
    if (Test-Command "winget") {
        winget install --id oschwartz10612.Poppler -e --accept-source-agreements --accept-package-agreements
    } else {
        Write-Warning "Install Poppler manually for scanned PDF OCR:"
        Write-Host "https://github.com/oschwartz10612/poppler-windows/releases"
    }
} else {
    Write-Host "Poppler (pdftotext) already installed." -ForegroundColor Green
}

Write-Host ""
Write-Host "Add to your .env (adjust paths if needed):" -ForegroundColor Cyan
Write-Host @"
QUEUE_CONNECTION=database
OCR_QUEUE_CONNECTION=database
OCR_QUEUE=ocr
OCR_LANGUAGES=ara+eng
TESSERACT_BINARY=C:\Program Files\Tesseract-OCR\tesseract.exe
PDFTOTEXT_BINARY=C:\Program Files\poppler\Library\bin\pdftotext.exe
PDFTOPPM_BINARY=C:\Program Files\poppler\Library\bin\pdftoppm.exe
"@

Write-Host ""
Write-Host "Verify Arabic language pack:" -ForegroundColor Cyan
Write-Host "  tesseract --list-langs   (must include ara and eng)"
Write-Host ""
Write-Host "Start queue worker:" -ForegroundColor Cyan
Write-Host "  php artisan queue:work database --queue=ocr,default --tries=3 --timeout=600"
Write-Host ""
Write-Host "Health check:" -ForegroundColor Cyan
Write-Host "  php artisan ocr:health"
