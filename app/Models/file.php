<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'code', 'document_number', 'file_name', 'description', 'folder_id', 'file',
        'dep_id', 'category_id', 'document_type_id', 'user_id', 'owner_id',
        'role_id', 'status_id', 'approved_by', 'approved_at', 'expiry_date',
        'archive_date', 'qr_code_path', 'notes', 'ocr_text', 'current_version',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'expiry_date' => 'date',
        'archive_date' => 'date',
        'current_version' => 'integer',
    ];

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'folder_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'dep_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'document_tag', 'file_id', 'tag_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class, 'file_id')->orderByDesc('version_number');
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(DocumentTransfer::class, 'file_id')->latest();
    }

    public function workflowLogs(): HasMany
    {
        return $this->hasMany(DocumentWorkflowLog::class, 'file_id')->latest();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(DocumentComment::class, 'file_id')->latest();
    }

    public function resolveStorageDisk(): string
    {
        if (Storage::disk('local')->exists($this->file)) {
            return 'local';
        }
        if (Storage::disk('s3')->exists($this->file)) {
            return 's3';
        }

        return Storage::disk('public')->exists($this->file) ? 'public' : 'local';
    }

    public function resolveEdmsDisk(): string
    {
        return (string) config('filesystems.edms_disk', 'local');
    }

    public function qrCodeUrl(): ?string
    {
        if (! $this->qr_code_path) {
            return null;
        }

        return route('document.qr', $this);
    }

    public function qrPrintUrl(string $size = 'label'): string
    {
        return route('document.qr.print', ['file' => $this->id, 'size' => $size]);
    }

    public function qrDownloadUrl(): ?string
    {
        if (! $this->qr_code_path) {
            return null;
        }

        return route('document.qr', $this).'?download=1';
    }

    public function statusLabel(): string
    {
        return $this->status?->label() ?? '';
    }
}
