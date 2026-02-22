<?php

namespace App\Policies;

use App\Models\User;

class IncomingInvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('incoming_invoices', 'view');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('incoming_invoices', 'view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('incoming_invoices', 'create');
    }

    public function update(User $user): bool
    {
        return $user->hasPermission('incoming_invoices', 'update');
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('incoming_invoices', 'delete');
    }
}
