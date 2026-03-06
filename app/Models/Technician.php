<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Technician extends Model
{
    protected $fillable = [
        'branch_id', 'user_id', 'name', 'phone', 'email',
        'speciality', 'hourly_rate', 'is_active',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'hourly_rate' => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class, 'assigned_technician_id');
    }

    public function getActiveJobsCountAttribute(): int
    {
        return $this->serviceRequests()
            ->whereNotIn('status', ['delivered', 'cancelled'])
            ->count();
    }
}
