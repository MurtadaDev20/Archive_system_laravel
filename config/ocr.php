<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OCR queue
    |--------------------------------------------------------------------------
    */
    'queue' => env('OCR_QUEUE', 'ocr'),
    // OCR دائماً عبر database حتى لو QUEUE_CONNECTION=sync (لتشغيل queue:work)
    'queue_connection' => env('OCR_QUEUE_CONNECTION', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Default languages (Tesseract format: lang1+lang2)
    |--------------------------------------------------------------------------
    | ara = Arabic, eng = English
    */
    'languages' => env('OCR_LANGUAGES', 'ara+eng'),

    /*
    |--------------------------------------------------------------------------
    | Supported extensions for OCR processing
    |--------------------------------------------------------------------------
    */
    'supported_extensions' => ['pdf', 'jpg', 'jpeg', 'png', 'tif', 'tiff', 'webp'],

    'skipped_extensions' => ['doc', 'docx', 'xls', 'xlsx'],

    /*
    |--------------------------------------------------------------------------
    | Tesseract OCR
    |--------------------------------------------------------------------------
    */
    'tesseract' => [
        'binary' => env('TESSERACT_BINARY', null),
        // مجلد tessdata (يحتوي ara.traineddata مباشرة) — يُكتشف تلقائياً إن تُرك فارغاً
        'tessdata' => env('TESSDATA_PREFIX', storage_path('app/tesseract/tessdata')),
        'psm' => (int) env('TESSERACT_PSM', 3),
        'oem' => (int) env('TESSERACT_OEM', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Poppler utilities (PDF text / page rasterization)
    |--------------------------------------------------------------------------
    */
    'poppler' => [
        'pdftotext' => env('PDFTOTEXT_BINARY', null),
        'pdftoppm' => env('PDFTOPPM_BINARY', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | PDF processing
    |--------------------------------------------------------------------------
    */
    'pdf' => [
        'min_text_length' => (int) env('OCR_PDF_MIN_TEXT_LENGTH', 40),
        'max_ocr_pages' => (int) env('OCR_PDF_MAX_PAGES', 20),
        'page_dpi' => (int) env('OCR_PDF_PAGE_DPI', 200),
    ],

    /*
    |--------------------------------------------------------------------------
    | Job settings
    |--------------------------------------------------------------------------
    */
    'job' => [
        'tries' => (int) env('OCR_JOB_TRIES', 3),
        'timeout' => (int) env('OCR_JOB_TIMEOUT', 600),
        'backoff' => [30, 120, 300],
    ],

    /*
    |--------------------------------------------------------------------------
    | Known binary paths (auto-detected when env is empty)
    |--------------------------------------------------------------------------
    */
    'binary_candidates' => [
        'tesseract' => [
            'C:\\Program Files\\Tesseract-OCR\\tesseract.exe',
            'C:\\Program Files (x86)\\Tesseract-OCR\\tesseract.exe',
            '/usr/bin/tesseract',
            '/usr/local/bin/tesseract',
        ],
        'pdftotext' => [
            'C:\\Program Files\\poppler\\Library\\bin\\pdftotext.exe',
            'C:\\poppler\\Library\\bin\\pdftotext.exe',
            '/usr/bin/pdftotext',
            '/usr/local/bin/pdftotext',
        ],
        'pdftoppm' => [
            'C:\\Program Files\\poppler\\Library\\bin\\pdftoppm.exe',
            'C:\\poppler\\Library\\bin\\pdftoppm.exe',
            '/usr/bin/pdftoppm',
            '/usr/local/bin/pdftoppm',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Language registry — add new Tesseract language packs here
    |--------------------------------------------------------------------------
    */
    'available_languages' => [
        'ara' => 'العربية',
        'eng' => 'English',
        'fra' => 'Français',
        'deu' => 'Deutsch',
    ],

];
