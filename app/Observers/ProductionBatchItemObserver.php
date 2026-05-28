<?php

namespace App\Observers;

use App\Models\ProductionBatchItem;
use App\Services\ProductionBatchService;

class ProductionBatchItemObserver
{
    public function saved(ProductionBatchItem $productionBatchItem): void
    {
        if (! $productionBatchItem->productionBatch) {
            return;
        }

        app(ProductionBatchService::class)->syncMaterialOrdersFromSummary($productionBatchItem->productionBatch);
    }

    public function deleted(ProductionBatchItem $productionBatchItem): void
    {
        if (! $productionBatchItem->productionBatch) {
            return;
        }

        app(ProductionBatchService::class)->syncMaterialOrdersFromSummary($productionBatchItem->productionBatch);
    }
}
