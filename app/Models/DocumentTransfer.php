<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentTransfer extends Model
{
    public const STATUS_SENT = 'sent';
    public const STATUS_RECEIVED = 'received';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'file_id', 'from_department_id', 'to_department_id',
        'from_user_id', 'to_user_id', 'status', 'comment', 'response_comment',
        'sent_at', 'received_at', 'responded_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'received_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(File::class, 'file_id');
    }

    public function fromDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'from_department_id');
    }

    public function toDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'to_department_id');
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }
}
