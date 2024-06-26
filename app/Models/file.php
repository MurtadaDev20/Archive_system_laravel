<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class file extends Model
{
    use HasFactory;

    protected $fillable = ['code','file_name', 'folder_id', 'file','dep_id','user_id','role_id','status_id'];

    public function folder()
    {
        return $this->belongsTo(Folder::class , 'folder_id');
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

    public function status()
    {
        return $this->belongsTo(Status::class , 'status_id');
    }
}
