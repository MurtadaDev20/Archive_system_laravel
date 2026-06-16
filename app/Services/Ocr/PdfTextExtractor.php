<?php

namespace App\Services\Ocr;

use Illuminate\Support\Facades\File as FileFacade;
use Illuminate\Support\Str;
use RuntimeException;
use Smalot\PdfParser\Parser as PdfParser;
use Spatie\PdfToText\Pdf;

class PdfTextExtractor
{
    public function __construct(
        private readonly OcrBinaryResolver $binaries,
        private readonly TesseractOcrEngine $tesseract
    ) {}

    /**
     * @return array{text: string, page_count: int|null, method: string}
     */
    public function extract(string $pdfPath, ?string $languages = null): array
    {
        if (! is_file($pdfPath)) {
            throw new RuntimeException("PDF file not found: {$pdfPath}");
        }

        $text = $this->extractEmbeddedText($pdfPath);
        $pageCount = $this->estimatePageCount($pdfPath);

        if ($this->isMeaningfulText($text)) {
            return [
                'text' => $text,
                'page_count' => $pageCount,
                'method' => 'pdf_text_layer',
            ];
        }

        if (! $this->binaries->isPdftoppmAvailable() || ! $this->binaries->isTesseractAvailable()) {
            if (filled(trim($text))) {
                return [
                    'text' => $text,
                    'page_count' => $pageCount,
                    'method' => 'pdf_parser_partial',
                ];
            }

            throw new RuntimeException(
                'Scanned PDF requires Poppler (pdftoppm) and Tesseract. Install poppler-utils and Tesseract OCR.'
            );
        }

        $ocrText = $this->ocrPdfPages($pdfPath, $languages);
        $combined = trim($text."\n".$ocrText);

        return [
            'text' => $combined,
            'page_count' => $pageCount,
            'method' => 'pdf_ocr',
        ];
    }

    private function extractEmbeddedText(string $pdfPath): string
    {
        $chunks = [];

        try {
            $parser = new PdfParser;
            $pdf = $parser->parseFile($pdfPath);
            $chunks[] = trim((string) $pdf->getText());
        } catch (\Throwable) {
            // fall through to pdftotext
        }

        if ($this->binaries->isPdftotextAvailable()) {
            try {
                $pdftotext = $this->binaries->pdftotextBinary();
                $chunks[] = trim(Pdf::getText($pdfPath, $pdftotext));
            } catch (\Throwable) {
                // ignore
            }
        }

        return trim(implode("\n", array_filter($chunks)));
    }

    private function ocrPdfPages(string $pdfPath, ?string $languages): string
    {
        $maxPages = (int) config('ocr.pdf.max_ocr_pages', 20);
        $dpi = (int) config('ocr.pdf.page_dpi', 200);
        $pdftoppm = $this->binaries->pdftoppmBinary();

        $tempDir = storage_path('app/ocr-temp/'.Str::uuid());
        FileFacade::ensureDirectoryExists($tempDir);

        try {
            $prefix = $tempDir.DIRECTORY_SEPARATOR.'page';
            $command = sprintf(
                '"%s" -png -r %d -f 1 -l %d "%s" "%s"',
                $pdftoppm,
                $dpi,
                $maxPages,
                $pdfPath,
                $prefix
            );

            $this->runCommand($command);

            $images = glob($prefix.'-*.png') ?: glob($prefix.'*.png') ?: [];
            sort($images);

            $parts = [];
            foreach ($images as $image) {
                $parts[] = $this->tesseract->extractFromImage($image, $languages);
            }

            return trim(implode("\n\n", array_filter($parts)));
        } finally {
            FileFacade::deleteDirectory($tempDir);
        }
    }

    private function estimatePageCount(string $pdfPath): ?int
    {
        try {
            $parser = new PdfParser;
            $pdf = $parser->parseFile($pdfPath);

            return count($pdf->getPages());
        } catch (\Throwable) {
            return null;
        }
    }

    private function isMeaningfulText(string $text): bool
    {
        $normalized = preg_replace('/\s+/u', '', $text) ?? '';

        return mb_strlen($normalized) >= (int) config('ocr.pdf.min_text_length', 40);
    }

    private function runCommand(string $command): void
    {
        $output = [];
        $exitCode = 0;
        exec($command.' 2>&1', $output, $exitCode);

        if ($exitCode !== 0) {
            throw new RuntimeException('PDF rasterization failed: '.implode("\n", $output));
        }
    }
}
