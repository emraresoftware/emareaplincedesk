<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ServiceRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'branch_id', 'ticket_no', 'customer_id', 'device_id',
        'assigned_technician_id', 'created_by',
        'status', 'priority', 'type',
        'problem_description', 'diagnosis', 'solution', 'internal_notes',
        'device_condition_in', 'accessories_received',
        'estimated_cost', 'labor_cost', 'parts_cost', 'discount', 'total_cost',
        'received_at', 'estimated_completion_at', 'completed_at', 'delivered_at',
        'customer_approval', 'customer_signature',
    ];

    protected $casts = [
        'customer_approval' => 'boolean',
        'received_at' => 'datetime',
        'estimated_completion_at' => 'datetime',
        'completed_at' => 'datetime',
        'delivered_at' => 'datetime',
        'estimated_cost' => 'decimal:2',
        'labor_cost' => 'decimal:2',
        'parts_cost' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    const STATUSES = [
        'pending'      => ['label' => 'Beklemede',        'color' => 'yellow', 'icon' => 'fa-clock'],
        'diagnosed'    => ['label' => 'Teşhis Yapıldı',   'color' => 'blue',   'icon' => 'fa-stethoscope'],
        'in_progress'  => ['label' => 'İşlemde',          'color' => 'indigo', 'icon' => 'fa-wrench'],
        'waiting_part' => ['label' => 'Parça Bekleniyor', 'color' => 'orange', 'icon' => 'fa-box-open'],
        'ready'        => ['label' => 'Teslime Hazır',    'color' => 'green',  'icon' => 'fa-check-circle'],
        'delivered'    => ['label' => 'Teslim Edildi',    'color' => 'gray',   'icon' => 'fa-handshake'],
        'cancelled'    => ['label' => 'İptal',            'color' => 'red',    'icon' => 'fa-times-circle'],
    ];

    const PRIORITIES = [
        'low'    => ['label' => 'Düşük',   'color' => 'gray'],
        'normal' => ['label' => 'Normal',  'color' => 'blue'],
        'high'   => ['label' => 'Yüksek',  'color' => 'orange'],
        'urgent' => ['label' => 'Acil',    'color' => 'red'],
    ];

    const TYPES = [
        'repair'       => 'Onarım',
        'maintenance'  => 'Bakım',
        'installation' => 'Kurulum',
        'inspection'   => 'Kontrol',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(Technician::class, 'assigned_technician_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ServiceNote::class)->latest();
    }

    public function spareParts(): HasMany
    {
        return $this->hasMany(SparePartUsage::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status]['label'] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUSES[$this->status]['color'] ?? 'gray';
    }

    public function getPriorityLabelAttribute(): string
    {
        return self::PRIORITIES[$this->priority]['label'] ?? $this->priority;
    }

    public static function generateTicketNo(int $branchId): string
    {
        $prefix = 'SRV-' . date('Y') . '-';
        $last = static::where('branch_id', $branchId)
            ->where('ticket_no', 'like', $prefix . '%')
            ->max('ticket_no');
        if ($last) {
            $number = (int) substr($last, strrpos($last, '-') + 1) + 1;
        } else {
            $number = 1;
        }
        return $prefix . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}
