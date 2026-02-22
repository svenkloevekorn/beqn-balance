<?php

namespace App\Policies;

use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('categories', 'view');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('categories', 'view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('categories', 'create');
    }

    public function update(User $user): bool
    {
        return $user->hasPermission('categories', 'update');
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('categories', 'delete');
    }
}
