<?php

namespace App\Services;

use App\Models\File;

class DocumentNumberService
{
    public function generate(): string
    {
        $year = now()->format('Y');
        $last = File::withTrashed()
            ->where('document_number', 'like', "DOC-{$year}-%")
            ->orderByDesc('id')
            ->value('document_number');

        $seq = 1;
        if ($last && preg_match('/DOC-'.$year.'-(\d+)/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return sprintf('DOC-%s-%06d', $year, $seq);
    }
}
