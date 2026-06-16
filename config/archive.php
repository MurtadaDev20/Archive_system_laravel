<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Realtime polling fallback (seconds)
    |--------------------------------------------------------------------------
    | 0 = disabled (use WebSocket/Echo only)
    | When BROADCAST_DRIVER is not pusher, defaults to 30 seconds.
    */
    'realtime_poll_seconds' => (int) env(
        'ARCHIVE_POLL_SECONDS',
        env('BROADCAST_DRIVER', 'null') === 'pusher' ? 0 : 30
    ),

    /*
    |--------------------------------------------------------------------------
    | OCR status polling on document detail (seconds, 0 = disabled)
    |--------------------------------------------------------------------------
    */
    'ocr_poll_seconds' => (int) env('ARCHIVE_OCR_POLL_SECONDS', 10),

];
