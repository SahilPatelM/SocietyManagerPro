<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ParkingSlot extends Model
{
    protected $fillable = ['society_id', 'slot_number', 'status'];

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(ParkingAllocation::class, 'parking_slot_id');
    }

    public function activeAllocation(): HasOne
    {
        return $this->hasOne(ParkingAllocation::class, 'parking_slot_id')
            ->ofMany(['id' => 'max'], function (Builder $query) {
                $query->whereNull('allocated_until');
            });
    }
}
