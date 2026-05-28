<?php

namespace App\Policies;

use App\Models\ProductSet;
use App\Models\User;
use App\Support\Permission;

class ProductSetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::PRODUCTS_VIEW);
    }

    public function view(User $user, ProductSet $productSet): bool
    {
        return $user->can(Permission::PRODUCTS_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::PRODUCTS_MANAGE);
    }

    public function update(User $user, ProductSet $productSet): bool
    {
        return $user->can(Permission::PRODUCTS_MANAGE);
    }

    public function delete(User $user, ProductSet $productSet): bool
    {
        return $user->can(Permission::PRODUCTS_MANAGE);
    }
}
