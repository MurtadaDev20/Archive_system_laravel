<?php

namespace App\Services\Ocr;

class OcrBinaryResolver
{
    public function tesseractBinary(): ?string
    {
        return $this->resolve('tesseract', config('ocr.tesseract.binary'));
    }

    public function pdftotextBinary(): ?string
    {
        return $this->resolve('pdftotext', config('ocr.poppler.pdftotext'));
    }

    public function pdftoppmBinary(): ?string
    {
        return $this->resolve('pdftoppm', config('ocr.poppler.pdftoppm'));
    }

    public function isTesseractAvailable(): bool
    {
        return $this->tesseractBinary() !== null;
    }

    public function isPdftotextAvailable(): bool
    {
        return $this->pdftotextBinary() !== null;
    }

    public function isPdftoppmAvailable(): bool
    {
        return $this->pdftoppmBinary() !== null;
    }

    /** المجلد الذي يحتوي ملفات *.traineddata مباشرة */
    public function resolveTessdataPrefix(): ?string
    {
        $configured = config('ocr.tesseract.tessdata');

        if ($configured) {
            $configured = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $configured), DIRECTORY_SEPARATOR);

            if ($this->hasLanguageFiles($configured)) {
                return $configured;
            }

            $nested = $configured.DIRECTORY_SEPARATOR.'tessdata';
            if ($this->hasLanguageFiles($nested)) {
                return $nested;
            }
        }

        $projectTessdata = storage_path('app/tesseract/tessdata');
        if ($this->hasLanguageFiles($projectTessdata)) {
            return $projectTessdata;
        }

        foreach (['C:\\Program Files\\Tesseract-OCR\\tessdata', '/usr/share/tesseract-ocr/5/tessdata', '/usr/share/tessdata'] as $systemPath) {
            if ($this->hasLanguageFiles($systemPath)) {
                return $systemPath;
            }
        }

        return null;
    }

    public function applyTessdataEnvironment(): void
    {
        $prefix = $this->resolveTessdataPrefix();

        if ($prefix) {
            putenv('TESSDATA_PREFIX='.$prefix);
        }
    }

    private function hasLanguageFiles(string $directory): bool
    {
        return is_dir($directory)
            && (is_file($directory.DIRECTORY_SEPARATOR.'eng.traineddata')
                || is_file($directory.DIRECTORY_SEPARATOR.'ara.traineddata'));
    }

    /**
     * @return array{tesseract: bool, pdftotext: bool, pdftoppm: bool, languages: string|null}
     */
    public function healthReport(): array
    {
        $tesseract = $this->tesseractBinary();
        $languages = null;

        if ($tesseract) {
            $this->applyTessdataEnvironment();
            $output = @shell_exec('"'.$tesseract.'" --list-langs 2>&1');
            $languages = is_string($output) ? trim($output) : null;
        }

        return [
            'tesseract' => $tesseract !== null,
            'tesseract_path' => $tesseract,
            'tessdata_prefix' => $this->resolveTessdataPrefix(),
            'pdftotext' => $this->pdftotextBinary() !== null,
            'pdftoppm' => $this->pdftoppmBinary() !== null,
            'languages' => $languages,
            'configured_languages' => config('ocr.languages'),
        ];
    }

    private function resolve(string $key, ?string $configured): ?string
    {
        if ($configured && $this->isExecutable($configured)) {
            return $configured;
        }

        foreach (config("ocr.binary_candidates.{$key}", []) as $candidate) {
            if ($this->isExecutable($candidate)) {
                return $candidate;
            }
        }

        $which = $this->which($key === 'tesseract' ? 'tesseract' : $key);

        return $which && $this->isExecutable($which) ? $which : null;
    }

    private function isExecutable(string $path): bool
    {
        return is_file($path) && is_readable($path);
    }

    private function which(string $command): ?string
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $output = @shell_exec('where '.$command.' 2>nul');

            if (! is_string($output) || trim($output) === '') {
                return null;
            }

            $line = trim(explode("\n", str_replace("\r", '', $output))[0]);

            return $line !== '' ? $line : null;
        }

        $output = @shell_exec('command -v '.$command.' 2>/dev/null');

        return is_string($output) && trim($output) !== '' ? trim($output) : null;
    }
}
