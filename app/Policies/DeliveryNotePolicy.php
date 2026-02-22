<?php

namespace App\Policies;

use App\Models\User;

class DeliveryNotePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('delivery_notes', 'view');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('delivery_notes', 'view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('delivery_notes', 'create');
    }

    public function update(User $user): bool
    {
        return $user->hasPermission('delivery_notes', 'update');
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('delivery_notes', 'delete');
    }
}
