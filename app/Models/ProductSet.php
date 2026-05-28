<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductSet extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
        'can_sell_as_set',
        'can_sell_items_separately',
        'set_price',
        'cost_price',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'can_sell_as_set' => 'boolean',
            'can_sell_items_separately' => 'boolean',
            'set_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProductSetItem::class);
    }

    public function getItemsSeparateTotalAttribute(): float
    {
        if ($this->relationLoaded('items')) {
            return (float) $this->items->sum(fn (ProductSetItem $item): float => $item->separate_total);
        }

        return (float) $this->items()->get()->sum(fn (ProductSetItem $item): float => $item->separate_total);
    }
}
