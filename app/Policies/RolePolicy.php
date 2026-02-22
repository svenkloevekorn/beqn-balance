<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('users', 'view');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('users', 'view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('users', 'create');
    }

    public function update(User $user, Role $role): bool
    {
        if ($role->is_super_admin && ! $user->isSuperAdmin()) {
            return false;
        }

        return $user->hasPermission('users', 'update');
    }

    public function delete(User $user, Role $role): bool
    {
        if ($role->is_super_admin) {
            return false;
        }

        if ($role->users()->count() > 0) {
            return false;
        }

        return $user->hasPermission('users', 'delete');
    }
}
