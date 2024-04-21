<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class folder extends Model
{
    use HasFactory;
    protected $fillable = ['folder_name','user_id', 'role_id','dep_id'];
    public function files()
    {
        return $this->hasMany(File::class,'folder_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'dep_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class , 'user_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class , 'role_id');
    }
}
