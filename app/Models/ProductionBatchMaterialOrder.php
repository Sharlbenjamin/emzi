<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionBatchMaterialOrder extends Model
{
    protected $fillable = [
        'production_batch_id',
        'material_id',
        'supplier_id',
        'required_quantity',
        'ordered_quantity',
        'received_quantity',
        'status',
        'ordered_at',
        'expected_delivery_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'required_quantity' => 'decimal:3',
            'ordered_quantity' => 'decimal:3',
            'received_quantity' => 'decimal:3',
            'ordered_at' => 'date',
            'expected_delivery_date' => 'date',
        ];
    }

    public function productionBatch(): BelongsTo
    {
        return $this->belongsTo(ProductionBatch::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
