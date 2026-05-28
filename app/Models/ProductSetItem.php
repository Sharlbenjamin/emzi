<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSetItem extends Model
{
    protected $fillable = [
        'product_set_id',
        'product_id',
        'product_variant_id',
        'quantity',
        'separate_sale_price',
        'set_allocation_price',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'separate_sale_price' => 'decimal:2',
            'set_allocation_price' => 'decimal:2',
        ];
    }

    public function productSet(): BelongsTo
    {
        return $this->belongsTo(ProductSet::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function getSeparateTotalAttribute(): float
    {
        $quantity = (int) ($this->quantity ?? 0);
        $price = (float) ($this->separate_sale_price ?? $this->product?->base_price ?? 0);

        return $quantity * $price;
    }
}
