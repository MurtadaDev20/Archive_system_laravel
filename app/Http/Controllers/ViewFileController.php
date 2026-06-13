<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Support\Facades\Storage;

class ViewFileController extends Controller
{
    public function view(int $file)
    {
        $data = File::with(['folder', 'status'])->findOrFail($file);
        $this->authorize('view', $data);

        return view('layouts.admin.viewFile', [
            'file' => $data,
            'streamUrl' => route('streamFile', $data),
        ]);
    }

    public function stream(int $file)
    {
        $data = File::with(['folder', 'status'])->findOrFail($file);
        $this->authorize('view', $data);

        $disk = $data->resolveStorageDisk();

        if (! Storage::disk($disk)->exists($data->file)) {
            abort(404, __('archive.file_not_found'));
        }

        return Storage::disk($disk)->response($data->file, $data->file_name);
    }

    public function qr(int $file)
    {
        $data = File::findOrFail($file);
        $this->authorize('view', $data);

        if (! $data->qr_code_path) {
            abort(404, __('archive.qr_not_found'));
        }

        $disk = $data->resolveEdmsDisk();

        if (! Storage::disk($disk)->exists($data->qr_code_path)) {
            abort(404, __('archive.qr_not_found'));
        }

        $headers = ['Content-Type' => 'image/png'];
        if (request()->boolean('download')) {
            $headers['Content-Disposition'] = 'attachment; filename="qr-'.($data->document_number ?? $data->id).'.png"';
        }

        return Storage::disk($disk)->response(
            $data->qr_code_path,
            'qr-'.($data->document_number ?? $data->id).'.png',
            $headers
        );
    }

    public function qrPrint(int $file)
    {
        $document = File::with(['department', 'category', 'documentType', 'status', 'folder', 'user'])
            ->findOrFail($file);

        $this->authorize('view', $document);

        app(\App\Services\DocumentQrService::class)->ensure($document);
        $document->refresh();

        $size = request()->query('size', 'label');
        if (! in_array($size, ['label', 'a4'], true)) {
            $size = 'label';
        }

        return view('documents.qr-label', [
            'document' => $document,
            'label' => app(\App\Services\DocumentQrService::class)->labelData($document),
            'size' => $size,
        ]);
    }
}
