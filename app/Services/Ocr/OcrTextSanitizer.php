<?php

namespace App\Services\Ocr;

class OcrTextSanitizer
{
    public function sanitize(string $text): string
    {
        if ($text === '') {
            return '';
        }

        $text = str_replace("\0", '', $text);

        if (function_exists('iconv')) {
            $clean = @iconv('UTF-8', 'UTF-8//IGNORE', $text);
            if ($clean !== false) {
                $text = $clean;
            }
        }

        if (! mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        }

        if (! mb_check_encoding($text, 'UTF-8')) {
            foreach (['CP1256', 'ISO-8859-1', 'Windows-1252'] as $encoding) {
                if (! function_exists('iconv')) {
                    break;
                }

                $converted = @iconv($encoding, 'UTF-8//IGNORE', $text);
                if ($converted !== false && mb_check_encoding($converted, 'UTF-8')) {
                    $text = $converted;
                    break;
                }
            }
        }

        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text) ?? $text;
        $text = preg_replace('/[\x{FFFD}]/u', '', $text) ?? $text;
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace("/[ \t]+/u", ' ', $text) ?? $text;
        $text = preg_replace("/\n{3,}/u", "\n\n", $text) ?? $text;

        return trim($text);
    }
}
