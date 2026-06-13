<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentVersion extends Model
{
    protected $fillable = [
        'file_id', 'version_number', 'storage_path', 'original_name',
        'mime_type', 'size', 'uploaded_by', 'change_notes',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(File::class, 'file_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
