<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'collection_id',
        'name',
        'handle',
        'description',
        'sku',
        'status',
        'shopify_product_id',
        'image_url',
        'base_price',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
        ];
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function productVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function billOfMaterials(): HasMany
    {
        return $this->hasMany(BillOfMaterial::class);
    }

    public function productionBatches(): HasMany
    {
        return $this->hasMany(ProductionBatch::class);
    }

    public function calculateProductionCost(): float
    {
        return (float) $this->billOfMaterials()
            ->with('material:id,cost_per_unit')
            ->get()
            ->sum(fn (BillOfMaterial $item): float => $item->line_cost);
    }

    public function getProductionCostAttribute(): float
    {
        if ($this->relationLoaded('billOfMaterials')) {
            return (float) $this->billOfMaterials->sum(fn (BillOfMaterial $item): float => $item->line_cost);
        }

        return $this->calculateProductionCost();
    }
}
