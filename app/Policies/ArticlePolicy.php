<?php

namespace App\Policies;

use App\Models\User;

class ArticlePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('articles', 'view');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('articles', 'view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('articles', 'create');
    }

    public function update(User $user): bool
    {
        return $user->hasPermission('articles', 'update');
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('articles', 'delete');
    }
}
