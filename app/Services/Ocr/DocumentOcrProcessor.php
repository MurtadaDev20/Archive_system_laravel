<?php

namespace App\Services\Ocr;

use App\Models\File;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentOcrProcessor
{
    public function __construct(
        private readonly TesseractOcrEngine $tesseract,
        private readonly PdfTextExtractor $pdfExtractor,
        private readonly OcrBinaryResolver $binaries,
        private readonly OcrTextSanitizer $sanitizer
    ) {}

    public function isSupported(File $document): bool
    {
        $extension = $this->extension($document);

        if (in_array($extension, config('ocr.skipped_extensions', []), true)) {
            return false;
        }

        return in_array($extension, config('ocr.supported_extensions', []), true);
    }

    public function queue(File $document, bool $force = false): void
    {
        if (! $this->isSupported($document)) {
            $document->update([
                'ocr_status' => File::OCR_SKIPPED,
                'ocr_processed_at' => now(),
                'ocr_error' => null,
            ]);

            return;
        }

        if ($force) {
            $document->update([
                'ocr_text' => null,
                'ocr_error' => null,
            ]);
        }

        $document->update([
            'ocr_status' => File::OCR_PENDING,
            'ocr_languages' => config('ocr.languages'),
        ]);

        \App\Jobs\ProcessDocumentOcrJob::dispatch($document->id, $force)
            ->onConnection(config('ocr.queue_connection'))
            ->onQueue(config('ocr.queue'));
    }

    public function process(int $fileId, bool $force = false): void
    {
        $document = File::query()->find($fileId);

        if (! $document || $document->file === 'pending' || ! $document->file) {
            return;
        }

        if (! $this->isSupported($document)) {
            $document->update([
                'ocr_status' => File::OCR_SKIPPED,
                'ocr_processed_at' => now(),
            ]);

            return;
        }

        if (! $force && $document->ocr_status === File::OCR_COMPLETED && filled($document->ocr_text)) {
            return;
        }

        $document->update([
            'ocr_status' => File::OCR_PROCESSING,
            'ocr_error' => null,
            'ocr_languages' => config('ocr.languages'),
        ]);

        try {
            $absolutePath = $this->absolutePath($document);
            $languages = $document->ocr_languages ?: config('ocr.languages');
            $extension = $this->extension($document);

            $result = match (true) {
                $extension === 'pdf' => $this->pdfExtractor->extract($absolutePath, $languages),
                in_array($extension, ['jpg', 'jpeg', 'png', 'tif', 'tiff', 'webp'], true) => [
                    'text' => $this->tesseract->extractFromImage($absolutePath, $languages),
                    'page_count' => 1,
                    'method' => 'tesseract_image',
                ],
                default => throw new \RuntimeException("Unsupported extension: {$extension}"),
            };

            $text = $this->normalizeText($result['text'] ?? '');

            if ($text === '') {
                $this->markFailed($document, __('archive.ocr_error_empty_text'), $result['page_count'] ?? null);

                return;
            }

            $this->markCompleted($document, $text, $result['page_count'] ?? null);
        } catch (\Throwable $e) {
            Log::error('OCR processing failed', [
                'file_id' => $document->id,
                'message' => $e->getMessage(),
            ]);

            $this->markFailed($document, Str::limit($e->getMessage(), 2000));
        }
    }

    private function markCompleted(File $document, string $text, ?int $pageCount): void
    {
        try {
            $document->update([
                'ocr_text' => $text,
                'ocr_status' => File::OCR_COMPLETED,
                'ocr_error' => null,
                'ocr_processed_at' => now(),
                'ocr_page_count' => $pageCount,
            ]);
        } catch (QueryException $e) {
            Log::warning('OCR text could not be saved to database, retrying after re-sanitize', [
                'file_id' => $document->id,
                'message' => $e->getMessage(),
            ]);

            $text = $this->sanitizer->sanitize($text);

            if ($text === '') {
                $this->markFailed($document, __('archive.ocr_error_encoding'));

                return;
            }

            try {
                $document->update([
                    'ocr_text' => $text,
                    'ocr_status' => File::OCR_COMPLETED,
                    'ocr_error' => null,
                    'ocr_processed_at' => now(),
                    'ocr_page_count' => $pageCount,
                ]);
            } catch (QueryException $retryException) {
                $this->markFailed($document, __('archive.ocr_error_encoding'));
            }
        }
    }

    private function markFailed(File $document, string $error, ?int $pageCount = null): void
    {
        $payload = [
            'ocr_status' => File::OCR_FAILED,
            'ocr_error' => Str::limit($error, 2000),
            'ocr_processed_at' => now(),
        ];

        if ($pageCount !== null) {
            $payload['ocr_page_count'] = $pageCount;
        }

        $document->update($payload);
    }

    public function absolutePath(File $document): string
    {
        $disk = Storage::disk($document->resolveStorageDisk());

        if (! $disk->exists($document->file)) {
            throw new \RuntimeException("Storage file missing: {$document->file}");
        }

        return $disk->path($document->file);
    }

    private function extension(File $document): string
    {
        return Str::lower(pathinfo($document->file, PATHINFO_EXTENSION));
    }

    private function normalizeText(string $text): string
    {
        return $this->sanitizer->sanitize($text);
    }
}
