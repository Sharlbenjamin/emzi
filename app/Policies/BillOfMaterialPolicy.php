<?php

namespace App\Policies;

use App\Models\BillOfMaterial;
use App\Models\User;
use App\Support\Permission;

class BillOfMaterialPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::BOM_VIEW);
    }

    public function view(User $user, BillOfMaterial $billOfMaterial): bool
    {
        return $user->can(Permission::BOM_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::BOM_MANAGE);
    }

    public function update(User $user, BillOfMaterial $billOfMaterial): bool
    {
        return $user->can(Permission::BOM_MANAGE);
    }

    public function delete(User $user, BillOfMaterial $billOfMaterial): bool
    {
        return $user->can(Permission::BOM_MANAGE);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(Permission::BOM_MANAGE);
    }
}
