<?php

namespace App\Policies;

use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('invoices', 'view');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('invoices', 'view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('invoices', 'create');
    }

    public function update(User $user): bool
    {
        return $user->hasPermission('invoices', 'update');
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('invoices', 'delete');
    }
}
