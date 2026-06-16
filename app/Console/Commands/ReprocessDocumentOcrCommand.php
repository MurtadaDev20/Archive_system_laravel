<?php

namespace App\Console\Commands;

use App\Models\File;
use App\Services\Ocr\DocumentOcrProcessor;
use Illuminate\Console\Command;

class ReprocessDocumentOcrCommand extends Command
{
    protected $signature = 'ocr:reprocess
                            {file? : Document ID — omit to reprocess all failed/pending}
                            {--force : Reprocess even if OCR already completed}
                            {--status=* : Filter by ocr_status (pending, failed)}';

    protected $description = 'Queue OCR reprocessing for one or more documents';

    public function handle(DocumentOcrProcessor $processor): int
    {
        $fileId = $this->argument('file');
        $force = (bool) $this->option('force');

        if ($fileId) {
            $document = File::findOrFail($fileId);
            $processor->queue($document, $force);
            $this->info("Queued OCR for document #{$document->id}");

            return self::SUCCESS;
        }

        $statuses = $this->option('status') ?: [File::OCR_PENDING, File::OCR_FAILED];

        $query = File::query()
            ->whereIn('ocr_status', $statuses)
            ->whereNotNull('file')
            ->where('file', '!=', 'pending');

        $count = 0;
        $query->orderBy('id')->chunkById(50, function ($documents) use ($processor, $force, &$count) {
            foreach ($documents as $document) {
                if (! $processor->isSupported($document)) {
                    continue;
                }

                $processor->queue($document, $force);
                $count++;
            }
        });

        $this->info("Queued {$count} document(s) for OCR processing.");

        return self::SUCCESS;
    }
}
