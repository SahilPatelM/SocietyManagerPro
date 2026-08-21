<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Visitor extends Model
{
    protected $fillable = [
        'society_id', 'house_id', 'visitor_name', 'mobile',
        'vehicle_number', 'entry_time', 'exit_time', 'logged_by',
    ];

    protected function casts(): array
    {
        return [
            'entry_time' => 'datetime',
            'exit_time' => 'datetime',
        ];
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }
}
