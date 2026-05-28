<?php

namespace App\Policies;

use App\Models\Material;
use App\Models\User;
use App\Support\Permission;

class MaterialPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::MATERIALS_VIEW);
    }

    public function view(User $user, Material $material): bool
    {
        return $user->can(Permission::MATERIALS_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::MATERIALS_CREATE);
    }

    public function update(User $user, Material $material): bool
    {
        return $user->can(Permission::MATERIALS_UPDATE);
    }

    public function delete(User $user, Material $material): bool
    {
        return $user->can(Permission::MATERIALS_DELETE);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(Permission::MATERIALS_DELETE);
    }
}
