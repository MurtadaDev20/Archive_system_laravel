<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class department extends Model
{
    use HasFactory;

    protected $fillable = ['dep_name','user_id', 'role_id','manager_id'];

    public function folders()
    {
        return $this->hasMany(Folder::class,'dep_id');
    }
    public function files()
    {
        return $this->hasMany(file::class,'dep_id');
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
