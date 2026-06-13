<?php

namespace App\Services;

use App\Models\File;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\Storage;

class DocumentQrService
{
    public function generate(File $document, bool $force = false): string
    {
        $disk = (string) config('filesystems.edms_disk', 'local');
        $relativePath = "documents/qr/{$document->id}.png";

        if (! $force && $document->qr_code_path && Storage::disk($disk)->exists($document->qr_code_path)) {
            return $document->qr_code_path;
        }

        $payload = $this->payload($document);

        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'scale' => 10,
            'imageBase64' => false,
            'addQuietzone' => true,
        ]);

        $png = (new QRCode($options))->render($payload);

        Storage::disk($disk)->put($relativePath, $png);

        $document->update(['qr_code_path' => $relativePath]);

        return $relativePath;
    }

    public function ensure(File $document): string
    {
        $disk = (string) config('filesystems.edms_disk', 'local');

        if ($document->qr_code_path && Storage::disk($disk)->exists($document->qr_code_path)) {
            return $document->qr_code_path;
        }

        return $this->generate($document);
    }

    public function regenerate(File $document): string
    {
        return $this->generate($document, true);
    }

    public function payload(File $document): string
    {
        return route('document.show', $document);
    }

    public function labelData(File $document): array
    {
        $document->loadMissing(['department', 'category', 'documentType', 'status', 'folder', 'user']);

        return [
            'organization' => config('app.name', __('archive.app_full_name')),
            'document_number' => $document->document_number ?? '—',
            'code' => $document->code ?? '—',
            'title' => $document->file_name,
            'department' => $document->department?->dep_name ?? '—',
            'category' => $document->category?->name ?? '—',
            'document_type' => $document->documentType?->name ?? '—',
            'folder' => $document->folder?->folder_name ?? '—',
            'status' => $document->statusLabel() ?: '—',
            'archive_date' => $document->archive_date?->format('Y-m-d') ?? $document->created_at?->format('Y-m-d') ?? '—',
            'uploaded_by' => $document->user?->name ?? '—',
            'url' => $this->payload($document),
            'generated_at' => now()->format('Y-m-d H:i'),
        ];
    }
}
