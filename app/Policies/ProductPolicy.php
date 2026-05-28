<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use App\Support\Permission;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::PRODUCTS_VIEW);
    }

    public function view(User $user, Product $product): bool
    {
        return $user->can(Permission::PRODUCTS_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::PRODUCTS_MANAGE);
    }

    public function update(User $user, Product $product): bool
    {
        return $user->can(Permission::PRODUCTS_MANAGE);
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->can(Permission::PRODUCTS_MANAGE);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(Permission::PRODUCTS_MANAGE);
    }
}
