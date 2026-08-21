<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaintenanceBill extends Model
{
    protected $fillable = [
        'society_id', 'house_id', 'bill_number', 'month_year', 'bill_type',
        'maintenance_amount', 'late_fee', 'due_date', 'status', 'paid_amount',
    ];

    protected function casts(): array
    {
        return [
            'maintenance_amount' => 'decimal:2',
            'late_fee' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'due_date' => 'date',
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

    public function payments(): HasMany
    {
        return $this->hasMany(MaintenancePayment::class);
    }

    public function totalDue(): float
    {
        return (float) $this->maintenance_amount + (float) $this->late_fee;
    }

    public function balanceDue(): float
    {
        return max(0, $this->totalDue() - (float) $this->paid_amount);
    }

    public function refreshStatus(): void
    {
        $due = $this->totalDue();
        $paid = (float) $this->paid_amount;

        if ($paid <= 0) {
            $status = $this->due_date->isPast() ? 'overdue' : 'pending';
        } elseif ($paid >= $due) {
            $status = 'paid';
        } else {
            $status = 'partial';
        }

        $this->update(['status' => $status]);
    }
}

