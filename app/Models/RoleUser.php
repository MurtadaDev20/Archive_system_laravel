<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleUser extends Model
{
    use HasFactory;
    protected $table = 'role_user';
    protected $fillable = ['user_id', 'role_id'];

    public function role()
    {
        return $this->belongsTo(Role::class ,'role_id');
    }
    public function users()
    {
        return $this->belongsTo(User::class ,'user_id');
    }

    
}
