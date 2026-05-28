<?php

namespace App\Models;

use App\Observers\ProductionBatchItemObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([ProductionBatchItemObserver::class])]
class ProductionBatchItem extends Model
{
    protected $fillable = [
        'production_batch_id',
        'product_id',
        'product_variant_id',
        'quantity_planned',
        'quantity_completed',
        'unit_cost_snapshot',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'unit_cost_snapshot' => 'decimal:2',
        ];
    }

    public function productionBatch(): BelongsTo
    {
        return $this->belongsTo(ProductionBatch::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
