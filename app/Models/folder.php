<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Folder extends Model
{
    use HasFactory;

    protected $fillable = ['folder_name', 'user_id', 'role_id', 'dep_id'];

    public function files(): HasMany
    {
        return $this->hasMany(File::class, 'folder_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'dep_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}
