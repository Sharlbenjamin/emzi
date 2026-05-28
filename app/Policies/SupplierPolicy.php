<?php

namespace App\Policies;

use App\Models\Supplier;
use App\Models\User;
use App\Support\Permission;

class SupplierPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::SUPPLIERS_VIEW);
    }

    public function view(User $user, Supplier $supplier): bool
    {
        return $user->can(Permission::SUPPLIERS_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::SUPPLIERS_CREATE);
    }

    public function update(User $user, Supplier $supplier): bool
    {
        return $user->can(Permission::SUPPLIERS_UPDATE);
    }

    public function delete(User $user, Supplier $supplier): bool
    {
        return $user->can(Permission::SUPPLIERS_DELETE);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(Permission::SUPPLIERS_DELETE);
    }
}
