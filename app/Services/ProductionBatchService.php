<?php

namespace App\Services;

use App\Models\BillOfMaterial;
use App\Models\Material;
use App\Models\Product;
use App\Models\ProductionBatch;
use App\Models\ProductionBatchItem;
use App\Models\ProductionBatchMaterialOrder;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductionBatchService
{
    public function assertMaterialsAreSufficient(ProductionBatch $batch): void
    {
        $summary = $this->buildPlanningSummary($batch);

        foreach ($summary['materials'] as $materialLine) {
            if (($materialLine['shortfall'] ?? 0) > 0) {
                throw ValidationException::withMessages([
                    'status' => sprintf(
                        'Not enough material for "%s". Required %.3f, available %.3f.',
                        $materialLine['material_name'] ?? 'Unknown material',
                        $materialLine['required_quantity'] ?? 0,
                        $materialLine['available_quantity'] ?? 0
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
        $summary = $this->buildPlanningSummary($batch);

        DB::transaction(function () use ($batch, $summary): void {
            foreach ($summary['materials'] as $materialLine) {
                $materialId = $materialLine['material_id'] ?? null;
                $requiredQuantity = (float) ($materialLine['required_quantity'] ?? 0);

                if (! $materialId || $requiredQuantity <= 0) {
                    continue;
                }

                $material = Material::query()->find($materialId);

                if (! $material) {
                    continue;
                }

                StockMovement::create([
                    'type' => 'material_out',
                    'material_id' => $material->id,
                    'quantity' => $requiredQuantity,
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

        $batch->loadMissing('items.product.productVariants', 'items.productVariant', 'product.productVariants', 'productVariant');
        $items = $this->resolveBatchItems($batch);

        DB::transaction(function () use ($batch, $items): void {
            foreach ($items as $item) {
                $variant = $item['variant'];
                $quantityCompleted = $item['quantity_completed'];
                $productCost = $item['product_cost'];

                if (! $variant || $quantityCompleted <= 0) {
                    continue;
                }

                StockMovement::create([
                    'type' => 'product_in',
                    'product_variant_id' => $variant->id,
                    'quantity' => $quantityCompleted,
                    'unit_cost' => $productCost,
                    'reason' => 'Production completed',
                    'reference_type' => ProductionBatch::class,
                    'reference_id' => $batch->id,
                    'notes' => sprintf('Batch %s completed', $batch->batch_number),
                ]);
            }

            $batch->completed_at ??= now();
            $batch->finished_stock_added_at = now();
            $batch->saveQuietly();

            $this->syncMaterialOrdersFromSummary($batch);
        });
    }

    public function buildPlanningSummary(ProductionBatch $batch): array
    {
        $batch->loadMissing(
            'items.product.billOfMaterials.material.supplier',
            'product.billOfMaterials.material.supplier'
        );

        $items = $this->resolveBatchItems($batch);
        $materials = [];
        $productLines = [];

        foreach ($items as $item) {
            $product = $item['product'];
            $plannedUnits = $item['quantity_planned'];

            if (! $product || $plannedUnits <= 0) {
                continue;
            }

            $productLines[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'planned_units' => $plannedUnits,
                'unit_cost' => $product->production_cost,
                'line_cost' => $plannedUnits * $product->production_cost,
            ];

            foreach ($product->billOfMaterials as $bomItem) {
                $material = $bomItem->material;

                if (! $material) {
                    continue;
                }

                $required = $this->requiredMaterialQuantityForBatch($bomItem, $plannedUnits);

                if (! isset($materials[$material->id])) {
                    $materials[$material->id] = [
                        'material_id' => $material->id,
                        'material_name' => $material->name,
                        'supplier_id' => $material->supplier_id,
                        'supplier_name' => $material->supplier?->name,
                        'required_quantity' => 0.0,
                        'available_quantity' => (float) ($material->available_quantity ?? 0),
                        'unit_cost' => (float) ($material->cost_per_unit ?? 0),
                    ];
                }

                $materials[$material->id]['required_quantity'] += $required;
            }
        }

        $materials = collect($materials)
            ->map(function (array $line): array {
                $line['shortfall'] = max(0, $line['required_quantity'] - $line['available_quantity']);
                $line['line_cost'] = $line['required_quantity'] * $line['unit_cost'];

                return $line;
            })
            ->values()
            ->all();

        $suppliersToCall = collect($materials)
            ->filter(fn (array $line): bool => ($line['shortfall'] ?? 0) > 0 && ! empty($line['supplier_name']))
            ->map(fn (array $line): array => [
                'supplier_id' => $line['supplier_id'],
                'supplier_name' => $line['supplier_name'],
                'material_name' => $line['material_name'],
                'shortfall' => $line['shortfall'],
            ])
            ->values()
            ->all();

        return [
            'products' => $productLines,
            'materials' => $materials,
            'suppliers_to_call' => $suppliersToCall,
            'total_planned_units' => collect($productLines)->sum('planned_units'),
            'total_production_cost' => collect($productLines)->sum('line_cost'),
            'total_material_cost' => collect($materials)->sum('line_cost'),
        ];
    }

    public function syncMaterialOrdersFromSummary(ProductionBatch $batch): void
    {
        $summary = $this->buildPlanningSummary($batch);

        foreach ($summary['materials'] as $materialLine) {
            $required = (float) ($materialLine['required_quantity'] ?? 0);
            $shortfall = (float) ($materialLine['shortfall'] ?? 0);

            if (($materialLine['material_id'] ?? null) === null || $required <= 0) {
                continue;
            }

            ProductionBatchMaterialOrder::query()->updateOrCreate(
                [
                    'production_batch_id' => $batch->id,
                    'material_id' => $materialLine['material_id'],
                ],
                [
                    'supplier_id' => $materialLine['supplier_id'] ?? null,
                    'required_quantity' => $required,
                    'ordered_quantity' => $shortfall,
                    'status' => $shortfall > 0 ? 'pending_order' : 'covered',
                ]
            );
        }
    }

    public function buildPreviewSummaryFromFormItems(array $rawItems): array
    {
        $products = Product::query()
            ->with(['billOfMaterials.material.supplier', 'productVariants'])
            ->whereIn('id', collect($rawItems)->pluck('product_id')->filter()->all())
            ->get()
            ->keyBy('id');

        $productLines = [];
        $materials = [];

        foreach ($rawItems as $rawItem) {
            $productId = (int) ($rawItem['product_id'] ?? 0);
            $planned = (int) ($rawItem['quantity_planned'] ?? 0);
            $completed = (int) ($rawItem['quantity_completed'] ?? 0);
            $variantId = (int) ($rawItem['product_variant_id'] ?? 0);

            if (! $productId || $planned <= 0 || ! isset($products[$productId])) {
                continue;
            }

            /** @var Product $product */
            $product = $products[$productId];
            /** @var ProductVariant|null $variant */
            $variant = $variantId ? $product->productVariants->firstWhere('id', $variantId) : null;

            $productLines[] = [
                'product_id' => $productId,
                'product_name' => $product->name,
                'planned_units' => $planned,
                'completed_units' => $completed > 0 ? $completed : $planned,
                'variant_sku' => $variant?->sku,
                'unit_cost' => $product->production_cost,
                'line_cost' => $planned * $product->production_cost,
            ];

            foreach ($product->billOfMaterials as $bomItem) {
                $material = $bomItem->material;

                if (! $material) {
                    continue;
                }

                $required = $this->requiredMaterialQuantityForBatch($bomItem, $planned);

                if (! isset($materials[$material->id])) {
                    $materials[$material->id] = [
                        'material_id' => $material->id,
                        'material_name' => $material->name,
                        'supplier_id' => $material->supplier_id,
                        'supplier_name' => $material->supplier?->name,
                        'required_quantity' => 0.0,
                        'available_quantity' => (float) ($material->available_quantity ?? 0),
                        'unit_cost' => (float) ($material->cost_per_unit ?? 0),
                    ];
                }

                $materials[$material->id]['required_quantity'] += $required;
            }
        }

        $materialRows = collect($materials)
            ->map(function (array $line): array {
                $line['shortfall'] = max(0, $line['required_quantity'] - $line['available_quantity']);
                $line['line_cost'] = $line['required_quantity'] * $line['unit_cost'];

                return $line;
            })
            ->values()
            ->all();

        $suppliersToCall = collect($materialRows)
            ->filter(fn (array $line): bool => ($line['shortfall'] ?? 0) > 0 && ! empty($line['supplier_name']))
            ->map(fn (array $line): array => [
                'supplier_name' => $line['supplier_name'],
                'material_name' => $line['material_name'],
                'shortfall' => $line['shortfall'],
            ])
            ->values()
            ->all();

        return [
            'products' => $productLines,
            'materials' => $materialRows,
            'suppliers_to_call' => $suppliersToCall,
            'total_planned_units' => collect($productLines)->sum('planned_units'),
            'total_production_cost' => collect($productLines)->sum('line_cost'),
            'total_material_cost' => collect($materialRows)->sum('line_cost'),
        ];
    }

    protected function requiredMaterialQuantityForBatch(BillOfMaterial $bomItem, int $plannedQuantity): float
    {
        return $bomItem->actual_required_quantity * $plannedQuantity;
    }

    protected function resolveBatchItems(ProductionBatch $batch): Collection
    {
        if ($batch->items->isNotEmpty()) {
            return $batch->items->map(function (ProductionBatchItem $item): array {
                $product = $item->product;
                $variant = $item->productVariant ?: $product?->productVariants()->where('is_active', true)->first();
                $planned = (int) ($item->quantity_planned ?? 0);
                $completed = (int) ($item->quantity_completed ?? 0);

                return [
                    'product' => $product,
                    'variant' => $variant,
                    'quantity_planned' => $planned,
                    'quantity_completed' => $completed > 0 ? $completed : $planned,
                    'product_cost' => (float) ($item->unit_cost_snapshot ?? $product?->production_cost ?? 0),
                ];
            });
        }

        // Backward compatibility with legacy single-product batch rows.
        if ($batch->product) {
            $planned = (int) ($batch->quantity_planned ?? 0);
            $completed = (int) ($batch->quantity_completed ?? 0);
            $variant = $batch->productVariant ?: $batch->product->productVariants()->where('is_active', true)->first();

            return collect([
                [
                    'product' => $batch->product,
                    'variant' => $variant,
                    'quantity_planned' => $planned,
                    'quantity_completed' => $completed > 0 ? $completed : $planned,
                    'product_cost' => (float) $batch->product->production_cost,
                ],
            ]);
        }

        return collect();
    }
}
