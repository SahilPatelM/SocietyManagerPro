<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceCycle extends Model
{
    protected $fillable = [
        'society_id', 'month_year', 'cycle_type', 'amount', 'late_fee', 'due_date',
        'bills_generated', 'notifications_sent_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'late_fee' => 'decimal:2',
            'due_date' => 'date',
            'bills_generated' => 'boolean',
            'notifications_sent_at' => 'datetime',
        ];
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
