<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillOfMaterial extends Model
{
    protected $fillable = [
        'product_id',
        'material_id',
        'quantity_required',
        'wastage_percentage',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity_required' => 'decimal:3',
            'wastage_percentage' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function getActualRequiredQuantityAttribute(): float
    {
        $required = (float) $this->quantity_required;
        $wastage = (float) $this->wastage_percentage;

        return $required + ($required * ($wastage / 100));
    }

    public function getLineCostAttribute(): float
    {
        $unitCost = (float) ($this->material?->cost_per_unit ?? 0);

        return $this->actual_required_quantity * $unitCost;
    }
}
