<?php

namespace App\Services;

use App\Models\File;
use App\Models\Folder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentStorageService
{
    public function store(File $document, UploadedFile $upload, ?Folder $folder = null): string
    {
        $folder = $folder ?: $document->folder;
        $departmentId = $folder?->dep_id ?? $document->dep_id ?? 'general';
        $year = now()->format('Y');
        $categorySlug = $document->category?->slug ?? 'uncategorized';
        $documentId = $document->id ?? 'temp';

        $directory = "documents/{$departmentId}/{$year}/{$categorySlug}/{$documentId}";
        $filename = Str::uuid().'.'.$upload->getClientOriginalExtension();

        return $upload->storeAs($directory, $filename, $this->disk());
    }

    public function storeVersion(File $document, UploadedFile $upload, int $versionNumber): string
    {
        $directory = dirname($document->file)."/versions/v{$versionNumber}";
        $filename = Str::uuid().'.'.$upload->getClientOriginalExtension();

        return $upload->storeAs($directory, $filename, $this->disk());
    }

    public function disk(): string
    {
        return config('filesystems.edms_disk', 'local');
    }

    public function delete(string $path): void
    {
        $disk = Storage::disk($this->disk());
        if ($disk->exists($path)) {
            $disk->delete($path);
        }
    }
}
