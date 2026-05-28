<?php

namespace App\Policies;

use App\Models\Collection;
use App\Models\User;
use App\Support\Permission;

class CollectionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::COLLECTIONS_VIEW);
    }

    public function view(User $user, Collection $collection): bool
    {
        return $user->can(Permission::COLLECTIONS_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::COLLECTIONS_CREATE);
    }

    public function update(User $user, Collection $collection): bool
    {
        return $user->can(Permission::COLLECTIONS_UPDATE);
    }

    public function delete(User $user, Collection $collection): bool
    {
        return $user->can(Permission::COLLECTIONS_DELETE);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(Permission::COLLECTIONS_DELETE);
    }
}
