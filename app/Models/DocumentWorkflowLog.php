<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentWorkflowLog extends Model
{
    protected $fillable = ['file_id', 'from_status_id', 'to_status_id', 'user_id', 'comment'];

    public function document(): BelongsTo
    {
        return $this->belongsTo(File::class, 'file_id');
    }

    public function fromStatus(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'from_status_id');
    }

    public function toStatus(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'to_status_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
