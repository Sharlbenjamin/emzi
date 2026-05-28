<?php

namespace App\Models;

use App\Observers\ProductionBatchObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([ProductionBatchObserver::class])]
class ProductionBatch extends Model
{
    protected $fillable = [
        'batch_number',
        'product_id',
        'product_variant_id',
        'quantity_planned',
        'quantity_completed',
        'status',
        'start_date',
        'expected_completion_date',
        'completed_at',
        'materials_deducted_at',
        'finished_stock_added_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'expected_completion_date' => 'date',
            'completed_at' => 'datetime',
            'materials_deducted_at' => 'datetime',
            'finished_stock_added_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function resolveVariantForStock(): ?ProductVariant
    {
        if ($this->productVariant) {
            return $this->productVariant;
        }

        return $this->product
            ?->productVariants()
            ->where('is_active', true)
            ->orderBy('id')
            ->first();
    }
}
