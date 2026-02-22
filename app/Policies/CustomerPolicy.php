<?php

namespace App\Policies;

use App\Models\User;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('customers', 'view');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('customers', 'view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('customers', 'create');
    }

    public function update(User $user): bool
    {
        return $user->hasPermission('customers', 'update');
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('customers', 'delete');
    }
}
