<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'branch_id', 'invoice_id', 'customer_id', 'created_by',
        'ref_no', 'amount', 'payment_method', 'currency', 'paid_at', 'notes',
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    const METHODS = [
        'cash'   => ['label' => 'Nakit',         'icon' => 'fa-money-bill'],
        'card'   => ['label' => 'Kredi/Banka', 'icon' => 'fa-credit-card'],
        'bank'   => ['label' => 'Banka Havalesi','icon' => 'fa-university'],
        'online' => ['label' => 'Online',        'icon' => 'fa-globe'],
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function booted(): void
    {
        static::created(function (Payment $payment) {
            $payment->invoice->recalculate();
        });

        static::deleted(function (Payment $payment) {
            $payment->invoice->recalculate();
        });
    }
}
