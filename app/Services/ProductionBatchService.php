<?php

namespace App\Services;

use App\Models\BillOfMaterial;
use App\Models\Material;
use App\Models\ProductionBatch;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductionBatchService
{
    public function assertMaterialsAreSufficient(ProductionBatch $batch): void
    {
        $batch->loadMissing('product.billOfMaterials.material');

        foreach ($batch->product->billOfMaterials as $bomItem) {
            $required = $this->requiredMaterialQuantityForBatch($bomItem, $batch->quantity_planned);
            $available = (float) ($bomItem->material?->available_quantity ?? 0);

            if ($available < $required) {
                throw ValidationException::withMessages([
                    'status' => sprintf(
                        'Not enough material for "%s". Required %.3f, available %.3f.',
                        $bomItem->material?->name ?? 'Unknown material',
                        $required,
                        $available
                    ),
                ]);
            }
        }
    }

    public function deductMaterialsForProduction(ProductionBatch $batch): void
    {
        if ($batch->materials_deducted_at) {
            return;
        }

        $this->assertMaterialsAreSufficient($batch);
        $batch->loadMissing('product.billOfMaterials.material');

        DB::transaction(function () use ($batch): void {
            foreach ($batch->product->billOfMaterials as $bomItem) {
                /** @var Material|null $material */
                $material = $bomItem->material;

                if (! $material) {
                    continue;
                }

                StockMovement::create([
                    'type' => 'material_out',
                    'material_id' => $material->id,
                    'quantity' => $this->requiredMaterialQuantityForBatch($bomItem, $batch->quantity_planned),
                    'unit_cost' => $material->cost_per_unit,
                    'reason' => 'Production consumption',
                    'reference_type' => ProductionBatch::class,
                    'reference_id' => $batch->id,
                    'notes' => sprintf('Batch %s started', $batch->batch_number),
                ]);
            }

            $batch->materials_deducted_at = now();
            $batch->saveQuietly();
        });
    }

    public function addFinishedStock(ProductionBatch $batch): void
    {
        if ($batch->finished_stock_added_at) {
            return;
        }

        $batch->loadMissing('product.productVariants', 'productVariant');

        $variant = $batch->resolveVariantForStock();

        if (! $variant) {
            throw ValidationException::withMessages([
                'product_variant_id' => 'No product variant found to receive finished stock.',
            ]);
        }

        if ($batch->quantity_completed <= 0) {
            $batch->quantity_completed = $batch->quantity_planned;
        }

        DB::transaction(function () use ($batch, $variant): void {
            StockMovement::create([
                'type' => 'product_in',
                'product_variant_id' => $variant->id,
                'quantity' => $batch->quantity_completed,
                'unit_cost' => $batch->product->production_cost,
                'reason' => 'Production completed',
                'reference_type' => ProductionBatch::class,
                'reference_id' => $batch->id,
                'notes' => sprintf('Batch %s completed', $batch->batch_number),
            ]);

            $batch->product_variant_id = $variant->id;
            $batch->completed_at ??= now();
            $batch->finished_stock_added_at = now();
            $batch->saveQuietly();
        });
    }

    protected function requiredMaterialQuantityForBatch(BillOfMaterial $bomItem, int $plannedQuantity): float
    {
        return $bomItem->actual_required_quantity * $plannedQuantity;
    }
}
