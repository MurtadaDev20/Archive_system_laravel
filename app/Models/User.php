<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles as SpatieHasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SpatieHasRoles {
        SpatieHasRoles::hasRole as spatieHasRole;
        SpatieHasRoles::hasAnyRole as spatieHasAnyRole;
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'manager_id',
        'department_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'manager_id' => 'integer',
        'department_id' => 'integer',
    ];

    public function legacyRoles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    public function folders(): HasMany
    {
        return $this->hasMany(Folder::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function managedDepartments(): HasMany
    {
        return $this->hasMany(Department::class, 'manager_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    public function hasRole(string $roleName): bool
    {
        $spatieAliases = [
            'Admin' => ['Admin', 'Super Admin'],
            'Manager' => ['Department Manager', 'Manager'],
            'Employee' => ['Employee'],
            'Editor' => ['Employee'],
        ];

        foreach ($spatieAliases[$roleName] ?? [$roleName] as $candidate) {
            if ($this->spatieHasRole($candidate)) {
                return true;
            }
        }

        return $this->legacyRoles()->where('name', $roleName)->exists();
    }

    public function hasAnyRole(array $roleNames): bool
    {
        foreach ($roleNames as $roleName) {
            if ($this->hasRole($roleName)) {
                return true;
            }
        }

        return false;
    }

    public function canPermission(string $permission): bool
    {
        return $this->can($permission);
    }
}
