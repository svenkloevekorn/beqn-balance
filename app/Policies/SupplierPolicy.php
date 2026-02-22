<?php

namespace App\Policies;

use App\Models\User;

class SupplierPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('suppliers', 'view');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('suppliers', 'view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('suppliers', 'create');
    }

    public function update(User $user): bool
    {
        return $user->hasPermission('suppliers', 'update');
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('suppliers', 'delete');
    }
}
