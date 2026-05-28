<?php

namespace App\Models;

use App\Observers\StockMovementObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([StockMovementObserver::class])]
class StockMovement extends Model
{
    public const TYPES = [
        'material_in',
        'material_out',
        'product_in',
        'product_reserved',
        'product_unreserved',
        'adjustment',
    ];

    protected $fillable = [
        'type',
        'material_id',
        'product_variant_id',
        'quantity',
        'unit_cost',
        'reason',
        'reference_type',
        'reference_id',
        'created_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_cost' => 'decimal:2',
        ];
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
