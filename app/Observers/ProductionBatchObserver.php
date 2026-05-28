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
        $service = app(ProductionBatchService::class);

        if ($productionBatch->wasChanged(['status', 'quantity_planned', 'quantity_completed', 'product_id', 'product_variant_id'])) {
            $service->syncMaterialOrdersFromSummary($productionBatch);
        }

        if (! $productionBatch->wasChanged('status')) {
            return;
        }

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
