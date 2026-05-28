<?php

namespace App\Observers;

use App\Models\StockMovement;

class StockMovementObserver
{
    public function creating(StockMovement $stockMovement): void
    {
        $stockMovement->created_by ??= request()->user()?->getAuthIdentifier();
    }

    public function created(StockMovement $stockMovement): void
    {
        $stockMovement->loadMissing('material', 'productVariant');

        if (blank($stockMovement->type)) {
            return;
        }

        match ($stockMovement->type) {
            'material_in' => $this->applyMaterialIn($stockMovement),
            'material_out' => $this->applyMaterialOut($stockMovement),
            'product_in' => $this->applyProductIn($stockMovement),
            'product_reserved' => $this->applyProductReserved($stockMovement),
            'product_unreserved' => $this->applyProductUnreserved($stockMovement),
            'adjustment' => $this->applyAdjustment($stockMovement),
            default => null,
        };
    }

    protected function applyMaterialIn(StockMovement $stockMovement): void
    {
        $material = $stockMovement->material;

        if (! $material || $stockMovement->quantity === null) {
            return;
        }

        $material->available_quantity += (float) $stockMovement->quantity;

        if ($stockMovement->unit_cost !== null) {
            $material->cost_per_unit = $stockMovement->unit_cost;
        }

        $material->save();
    }

    protected function applyMaterialOut(StockMovement $stockMovement): void
    {
        $material = $stockMovement->material;

        if (! $material || $stockMovement->quantity === null) {
            return;
        }

        $material->available_quantity -= (float) $stockMovement->quantity;
        $material->save();
    }

    protected function applyProductIn(StockMovement $stockMovement): void
    {
        $variant = $stockMovement->productVariant;

        if (! $variant || $stockMovement->quantity === null) {
            return;
        }

        $variant->available_stock += (int) round((float) $stockMovement->quantity);
        $variant->save();
    }

    protected function applyProductReserved(StockMovement $stockMovement): void
    {
        $variant = $stockMovement->productVariant;

        if (! $variant || $stockMovement->quantity === null) {
            return;
        }

        $quantity = (int) round((float) $stockMovement->quantity);

        $variant->available_stock -= $quantity;
        $variant->reserved_stock += $quantity;
        $variant->save();
    }

    protected function applyProductUnreserved(StockMovement $stockMovement): void
    {
        $variant = $stockMovement->productVariant;

        if (! $variant || $stockMovement->quantity === null) {
            return;
        }

        $quantity = (int) round((float) $stockMovement->quantity);

        $variant->reserved_stock -= $quantity;
        $variant->available_stock += $quantity;
        $variant->save();
    }

    protected function applyAdjustment(StockMovement $stockMovement): void
    {
        if ($stockMovement->material) {
            if ($stockMovement->quantity === null) {
                return;
            }

            $stockMovement->material->available_quantity += (float) $stockMovement->quantity;

            if ($stockMovement->unit_cost !== null) {
                $stockMovement->material->cost_per_unit = $stockMovement->unit_cost;
            }

            $stockMovement->material->save();

            return;
        }

        if ($stockMovement->productVariant) {
            if ($stockMovement->quantity === null) {
                return;
            }

            $stockMovement->productVariant->available_stock += (int) round((float) $stockMovement->quantity);
            $stockMovement->productVariant->save();
        }
    }
}
