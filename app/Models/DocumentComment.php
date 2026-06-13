<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentComment extends Model
{
    protected $fillable = ['file_id', 'user_id', 'body'];

    public function document(): BelongsTo
    {
        return $this->belongsTo(File::class, 'file_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
