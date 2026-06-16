<?php

namespace App\Services\Ocr;

use RuntimeException;
use thiagoalessio\TesseractOCR\TesseractOCR;

class TesseractOcrEngine
{
    public function __construct(
        private readonly OcrBinaryResolver $binaries
    ) {}

    public function extractFromImage(string $imagePath, ?string $languages = null): string
    {
        $binary = $this->binaries->tesseractBinary();

        if (! $binary) {
            throw new RuntimeException('Tesseract OCR binary not found. Install Tesseract and set TESSERACT_BINARY in .env');
        }

        if (! is_file($imagePath)) {
            throw new RuntimeException("Image file not found: {$imagePath}");
        }

        $this->binaries->applyTessdataEnvironment();

        $ocr = (new TesseractOCR($imagePath))
            ->executable($binary)
            ->lang($languages ?? config('ocr.languages'))
            ->psm((int) config('ocr.tesseract.psm', 3))
            ->oem((int) config('ocr.tesseract.oem', 3));

        return trim((string) $ocr->run());
    }
}
