<?php

namespace App\Jobs;

use App\Services\Ocr\DocumentOcrProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessDocumentOcrJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries;

    public int $timeout;

    /** @var array<int, int> */
    public array $backoff;

    public function __construct(
        public int $fileId,
        public bool $force = false
    ) {
        $this->tries = (int) config('ocr.job.tries', 3);
        $this->timeout = (int) config('ocr.job.timeout', 600);
        $this->backoff = config('ocr.job.backoff', [30, 120, 300]);
        $this->onConnection(config('ocr.queue_connection'));
        $this->onQueue(config('ocr.queue'));
    }

    public function handle(DocumentOcrProcessor $processor): void
    {
        $processor->process($this->fileId, $this->force);
    }
}
