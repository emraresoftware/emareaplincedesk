<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'branch_id', 'service_request_id', 'customer_id', 'created_by',
        'invoice_no', 'type', 'status', 'currency',
        'subtotal', 'tax_rate', 'tax_amount', 'discount', 'total',
        'paid_amount', 'remaining',
        'issue_date', 'due_date', 'notes',
    ];

    protected $casts = [
        'subtotal'    => 'decimal:2',
        'tax_rate'    => 'decimal:2',
        'tax_amount'  => 'decimal:2',
        'discount'    => 'decimal:2',
        'total'       => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining'   => 'decimal:2',
        'issue_date'  => 'date',
        'due_date'    => 'date',
    ];

    const STATUSES = [
        'draft'     => ['label' => 'Taslak',       'color' => 'gray'],
        'sent'      => ['label' => 'Gönderildi',   'color' => 'blue'],
        'paid'      => ['label' => 'Ödendi',        'color' => 'green'],
        'partial'   => ['label' => 'Kısmi Ödeme',  'color' => 'yellow'],
        'cancelled' => ['label' => 'İptal',         'color' => 'red'],
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function recalculate(): void
    {
        $subtotal = $this->items()->sum(\DB::raw('(quantity * unit_price) - discount'));
        $tax      = round($subtotal * ($this->tax_rate / 100), 2);
        $total    = $subtotal + $tax - $this->discount;
        $paid     = $this->payments()->sum('amount');
        $this->update([
            'subtotal'   => $subtotal,
            'tax_amount' => $tax,
            'total'      => max(0, $total),
            'paid_amount'=> $paid,
            'remaining'  => max(0, $total - $paid),
            'status'     => $paid >= $total ? 'paid' : ($paid > 0 ? 'partial' : $this->status),
        ]);
    }

    public static function generateInvoiceNo(int $branchId): string
    {
        $prefix = 'INV-' . date('Y') . '-';
        $last   = static::where('branch_id', $branchId)
            ->where('invoice_no', 'like', $prefix . '%')
            ->max('invoice_no');
        $number = $last ? (int) substr($last, strrpos($last, '-') + 1) + 1 : 1;
        return $prefix . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}
