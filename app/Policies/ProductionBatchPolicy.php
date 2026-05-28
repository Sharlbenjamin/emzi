<?php

namespace App\Policies;

use App\Models\ProductionBatch;
use App\Models\User;
use App\Support\Permission;

class ProductionBatchPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::PRODUCTION_BATCHES_VIEW);
    }

    public function view(User $user, ProductionBatch $productionBatch): bool
    {
        return $user->can(Permission::PRODUCTION_BATCHES_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::PRODUCTION_BATCHES_MANAGE);
    }

    public function update(User $user, ProductionBatch $productionBatch): bool
    {
        return $user->can(Permission::PRODUCTION_BATCHES_MANAGE);
    }

    public function delete(User $user, ProductionBatch $productionBatch): bool
    {
        return $user->can(Permission::PRODUCTION_BATCHES_MANAGE);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(Permission::PRODUCTION_BATCHES_MANAGE);
    }
}
