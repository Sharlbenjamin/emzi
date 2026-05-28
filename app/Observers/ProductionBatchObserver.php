<?php

namespace App\Observers;

use App\Models\ProductionBatch;
use App\Services\ProductionBatchService;

class ProductionBatchObserver
{
    public function updating(ProductionBatch $productionBatch): void
    {
        if (! $productionBatch->isDirty('status')) {
            return;
        }

        if ($productionBatch->status === 'in_production' && ! $productionBatch->materials_deducted_at) {
            app(ProductionBatchService::class)->assertMaterialsAreSufficient($productionBatch);
        }
    }

    public function updated(ProductionBatch $productionBatch): void
    {
        if (! $productionBatch->wasChanged('status')) {
            return;
        }

        $service = app(ProductionBatchService::class);

        if ($productionBatch->status === 'in_production') {
            $service->deductMaterialsForProduction($productionBatch);
        }

        if ($productionBatch->status === 'completed') {
            if (! $productionBatch->materials_deducted_at) {
                $service->deductMaterialsForProduction($productionBatch);
            }

            $service->addFinishedStock($productionBatch);
        }
    }
}
