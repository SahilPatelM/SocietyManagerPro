<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParkingAllocation extends Model
{
    protected $fillable = [
        'parking_slot_id', 'house_id', 'vehicle_number',
        'allocated_from', 'allocated_until',
    ];

    protected function casts(): array
    {
        return [
            'allocated_from' => 'date',
            'allocated_until' => 'date',
        ];
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(ParkingSlot::class, 'parking_slot_id');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('allocated_until');
    }

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }
}
