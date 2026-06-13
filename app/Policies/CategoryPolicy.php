<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Admin');
    }

    public function update(User $user, Category $category): bool
    {
        return $user->hasRole('Admin');
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->hasRole('Admin');
    }
}
