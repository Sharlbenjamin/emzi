<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    protected $fillable = [
        'name',
        'sku',
        'category',
        'supplier_id',
        'unit_type',
        'meters_per_roll',
        'kg_per_roll',
        'meters_per_piece',
        'available_quantity',
        'minimum_quantity_alert',
        'cost_per_unit',
        'color',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'available_quantity' => 'decimal:3',
            'minimum_quantity_alert' => 'decimal:3',
            'cost_per_unit' => 'decimal:2',
            'meters_per_roll' => 'decimal:3',
            'kg_per_roll' => 'decimal:3',
            'meters_per_piece' => 'decimal:3',
            'is_active' => 'boolean',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function billOfMaterials(): HasMany
    {
        return $this->hasMany(BillOfMaterial::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function productionBatchMaterialOrders(): HasMany
    {
        return $this->hasMany(ProductionBatchMaterialOrder::class);
    }

    public function getIsLowStockAttribute(): bool
    {
        return (float) $this->available_quantity <= (float) $this->minimum_quantity_alert;
    }

    public function getTotalMaterialValueAttribute(): float
    {
        return (float) $this->available_quantity * (float) $this->cost_per_unit;
    }
}
