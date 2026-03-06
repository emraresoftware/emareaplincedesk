<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'branch_id', 'customer_id', 'device_category_id',
        'brand', 'model', 'serial_no', 'imei', 'barcode',
        'manufacture_year', 'description', 'color', 'storage', 'condition',
        'is_under_warranty', 'warranty_expires_at',
    ];

    protected $casts = [
        'is_under_warranty' => 'boolean',
        'warranty_expires_at' => 'date',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DeviceCategory::class, 'device_category_id');
    }

    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->brand . ' ' . $this->model;
    }
}
