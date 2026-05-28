<?php

namespace App\Policies;

use App\Models\StockMovement;
use App\Models\User;
use App\Support\Permission;

class StockMovementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::STOCK_MOVEMENTS_VIEW);
    }

    public function view(User $user, StockMovement $stockMovement): bool
    {
        return $user->can(Permission::STOCK_MOVEMENTS_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::STOCK_MOVEMENTS_MANAGE);
    }

    public function update(User $user, StockMovement $stockMovement): bool
    {
        return false;
    }

    public function delete(User $user, StockMovement $stockMovement): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
