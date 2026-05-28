<?php

namespace App\Observers;

use App\Models\Material;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Validation\ValidationException;

class StockMovementObserver
{
    public function creating(StockMovement $stockMovement): void
    {
        $stockMovement->created_by ??= request()->user()?->getAuthIdentifier();

        if (! in_array($stockMovement->type, StockMovement::TYPES, true)) {
            throw ValidationException::withMessages([
                'type' => 'Invalid stock movement type.',
            ]);
        }

        if ((float) $stockMovement->quantity <= 0 && $stockMovement->type !== 'adjustment') {
            throw ValidationException::withMessages([
                'quantity' => 'Quantity must be greater than zero.',
            ]);
        }

        $material = $stockMovement->material_id ? Material::find($stockMovement->material_id) : null;
        $variant = $stockMovement->product_variant_id ? ProductVariant::find($stockMovement->product_variant_id) : null;

        match ($stockMovement->type) {
            'material_in' => $this->validateMaterialIn($material),
            'material_out' => $this->validateMaterialOut($stockMovement, $material),
            'product_in' => $this->validateProductIn($variant),
            'product_reserved' => $this->validateProductReserved($stockMovement, $variant),
            'product_unreserved' => $this->validateProductUnreserved($stockMovement, $variant),
            'adjustment' => $this->validateAdjustment($stockMovement, $material, $variant),
            default => null,
        };
    }

    public function created(StockMovement $stockMovement): void
    {
        $stockMovement->loadMissing('material', 'productVariant');

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

        $material->available_quantity += (float) $stockMovement->quantity;

        if ($stockMovement->unit_cost !== null) {
            $material->cost_per_unit = $stockMovement->unit_cost;
        }

        $material->save();
    }

    protected function applyMaterialOut(StockMovement $stockMovement): void
    {
        $material = $stockMovement->material;

        $material->available_quantity -= (float) $stockMovement->quantity;
        $material->save();
    }

    protected function applyProductIn(StockMovement $stockMovement): void
    {
        $variant = $stockMovement->productVariant;

        $variant->available_stock += (int) round((float) $stockMovement->quantity);
        $variant->save();
    }

    protected function applyProductReserved(StockMovement $stockMovement): void
    {
        $variant = $stockMovement->productVariant;
        $quantity = (int) round((float) $stockMovement->quantity);

        $variant->available_stock -= $quantity;
        $variant->reserved_stock += $quantity;
        $variant->save();
    }

    protected function applyProductUnreserved(StockMovement $stockMovement): void
    {
        $variant = $stockMovement->productVariant;
        $quantity = (int) round((float) $stockMovement->quantity);

        $variant->reserved_stock -= $quantity;
        $variant->available_stock += $quantity;
        $variant->save();
    }

    protected function applyAdjustment(StockMovement $stockMovement): void
    {
        if ($stockMovement->material) {
            $stockMovement->material->available_quantity += (float) $stockMovement->quantity;

            if ($stockMovement->unit_cost !== null) {
                $stockMovement->material->cost_per_unit = $stockMovement->unit_cost;
            }

            $stockMovement->material->save();

            return;
        }

        if ($stockMovement->productVariant) {
            $stockMovement->productVariant->available_stock += (int) round((float) $stockMovement->quantity);
            $stockMovement->productVariant->save();
        }
    }

    protected function validateMaterialIn(?Material $material): void
    {
        if ($material) {
            return;
        }

        throw ValidationException::withMessages([
            'material_id' => 'Material is required for material_in.',
        ]);
    }

    protected function validateMaterialOut(StockMovement $stockMovement, ?Material $material): void
    {
        if (! $material) {
            throw ValidationException::withMessages([
                'material_id' => 'Material is required for material_out.',
            ]);
        }

        if ((float) $material->available_quantity < (float) $stockMovement->quantity) {
            throw ValidationException::withMessages([
                'quantity' => sprintf('Material "%s" has insufficient stock.', $material->name),
            ]);
        }
    }

    protected function validateProductIn(?ProductVariant $variant): void
    {
        if ($variant) {
            return;
        }

        throw ValidationException::withMessages([
            'product_variant_id' => 'Product variant is required for product_in.',
        ]);
    }

    protected function validateProductReserved(StockMovement $stockMovement, ?ProductVariant $variant): void
    {
        if (! $variant) {
            throw ValidationException::withMessages([
                'product_variant_id' => 'Product variant is required for product_reserved.',
            ]);
        }

        $quantity = (int) round((float) $stockMovement->quantity);

        if ($variant->available_stock < $quantity) {
            throw ValidationException::withMessages([
                'quantity' => sprintf('Variant "%s" has insufficient available stock.', $variant->sku),
            ]);
        }
    }

    protected function validateProductUnreserved(StockMovement $stockMovement, ?ProductVariant $variant): void
    {
        if (! $variant) {
            throw ValidationException::withMessages([
                'product_variant_id' => 'Product variant is required for product_unreserved.',
            ]);
        }

        $quantity = (int) round((float) $stockMovement->quantity);

        if ($variant->reserved_stock < $quantity) {
            throw ValidationException::withMessages([
                'quantity' => sprintf('Variant "%s" has insufficient reserved stock.', $variant->sku),
            ]);
        }
    }

    protected function validateAdjustment(StockMovement $stockMovement, ?Material $material, ?ProductVariant $variant): void
    {
        if (! $material && ! $variant) {
            throw ValidationException::withMessages([
                'material_id' => 'Adjustment requires either a material or a product variant.',
            ]);
        }

        if ($material) {
            $updated = (float) $material->available_quantity + (float) $stockMovement->quantity;

            if ($updated < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Adjustment would result in negative material stock.',
                ]);
            }
        }

        if ($variant) {
            $updated = $variant->available_stock + (int) round((float) $stockMovement->quantity);

            if ($updated < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Adjustment would result in negative variant stock.',
                ]);
            }
        }
    }
}
