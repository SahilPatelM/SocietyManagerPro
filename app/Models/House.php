<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class House extends Model
{
    protected $fillable = [
        'society_id', 'block_id', 'house_number', 'status',
        'owner_user_id', 'outstanding_amount', 'paid_amount',
        'last_payment_date', 'qr_code',
    ];

    protected function casts(): array
    {
        return [
            'outstanding_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'last_payment_date' => 'date',
        ];
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function maintenanceBills(): HasMany
    {
        return $this->hasMany(MaintenanceBill::class);
    }

    public function maintenancePayments(): HasMany
    {
        return $this->hasMany(MaintenancePayment::class);
    }

    public function parkingAllocations(): HasMany
    {
        return $this->hasMany(ParkingAllocation::class);
    }

    public function hasActiveParking(): bool
    {
        return $this->parkingAllocations()
            ->whereNull('allocated_until')
            ->whereHas('slot', fn ($q) => $q->where('status', 'occupied'))
            ->exists();
    }
}
