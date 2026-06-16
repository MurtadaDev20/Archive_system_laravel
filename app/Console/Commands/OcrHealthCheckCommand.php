<?php

namespace App\Console\Commands;

use App\Models\File;
use App\Services\Ocr\DocumentOcrProcessor;
use App\Services\Ocr\OcrBinaryResolver;
use Illuminate\Console\Command;

class OcrHealthCheckCommand extends Command
{
    protected $signature = 'ocr:health';

    protected $description = 'Check OCR binaries (Tesseract, Poppler) and language packs';

    public function handle(OcrBinaryResolver $binaries): int
    {
        $report = $binaries->healthReport();

        $this->info('OCR Health Check');
        $this->line('────────────────');

        $this->line('Tesseract: '.($report['tesseract'] ? 'OK' : 'MISSING'));
        if ($report['tesseract_path']) {
            $this->line('  Path: '.$report['tesseract_path']);
        }
        if (! empty($report['tessdata_prefix'])) {
            $this->line('  Tessdata: '.$report['tessdata_prefix']);
        }

        $this->line('pdftotext: '.($report['pdftotext'] ? 'OK' : 'MISSING'));
        $this->line('pdftoppm: '.($report['pdftoppm'] ? 'OK' : 'MISSING'));
        $this->line('Configured languages: '.$report['configured_languages']);
        $ocrConnection = config('ocr.queue_connection');
        $this->line('OCR queue connection: '.$ocrConnection);

        if ($ocrConnection === 'sync') {
            $this->warn('OCR_QUEUE_CONNECTION=sync — jobs will NOT appear in the jobs table. Set OCR_QUEUE_CONNECTION=database');
        }

        if ($report['languages']) {
            $this->newLine();
            $this->line('Installed Tesseract languages:');
            $this->line($report['languages']);
        }

        $required = explode('+', (string) config('ocr.languages'));
        $installed = $report['languages'] ?? '';

        foreach ($required as $lang) {
            if ($lang && ! str_contains($installed, $lang)) {
                $this->warn("Language pack missing: {$lang}");
            }
        }

        return ($report['tesseract'] ?? false) ? self::SUCCESS : self::FAILURE;
    }
}
