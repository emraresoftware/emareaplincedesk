<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'branch_id', 'code', 'name', 'surname', 'company_name', 'type',
        'phone', 'phone2', 'email', 'address', 'city', 'country',
        'tax_number', 'tax_office', 'id_number', 'notes', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getFullNameAttribute(): string
    {
        if ($this->type === 'corporate' && $this->company_name) {
            return $this->company_name;
        }
        return trim($this->name . ' ' . $this->surname);
    }

    public function getOpenInvoicesTotalAttribute(): float
    {
        return $this->invoices()->whereNotIn('status', ['paid', 'cancelled'])->sum('remaining');
    }
}
