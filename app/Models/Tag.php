<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    protected $fillable = ['name', 'slug', 'color'];

    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'document_tag', 'tag_id', 'file_id');
    }
}
