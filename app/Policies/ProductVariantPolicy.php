<?php

namespace App\Policies;

use App\Models\ProductVariant;
use App\Models\User;
use App\Support\Permission;

class ProductVariantPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::PRODUCTS_VIEW);
    }

    public function view(User $user, ProductVariant $productVariant): bool
    {
        return $user->can(Permission::PRODUCTS_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::PRODUCTS_MANAGE);
    }

    public function update(User $user, ProductVariant $productVariant): bool
    {
        return $user->can(Permission::PRODUCTS_MANAGE);
    }

    public function delete(User $user, ProductVariant $productVariant): bool
    {
        return $user->can(Permission::PRODUCTS_MANAGE);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(Permission::PRODUCTS_MANAGE);
    }
}
