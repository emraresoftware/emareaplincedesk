<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SparePart extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'branch_id', 'code', 'name', 'brand', 'model_compatibility',
        'description', 'barcode', 'quantity', 'min_quantity',
        'purchase_price', 'sale_price', 'unit', 'location',
        'supplier', 'warranty_months', 'is_active',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'purchase_price' => 'decimal:2',
        'sale_price'     => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(SparePartUsage::class);
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->quantity <= $this->min_quantity;
    }
}
