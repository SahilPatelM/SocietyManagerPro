<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceBillResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'bill_number' => $this->bill_number,
            'month_year' => $this->month_year,
            'month_label' => \Carbon\Carbon::createFromFormat('Y-m', $this->month_year)->format('F Y'),
            'maintenance_amount' => $this->maintenance_amount,
            'late_fee' => $this->late_fee,
            'total_due' => $this->totalDue(),
            'paid_amount' => $this->paid_amount,
            'balance_due' => $this->balanceDue(),
            'due_date' => $this->due_date?->format('Y-m-d'),
            'status' => $this->status,
            'house' => $this->whenLoaded('house', fn () => [
                'id' => $this->house->id,
                'house_number' => $this->house->house_number,
                'owner' => $this->house->owner?->only(['id', 'name', 'mobile']),
            ]),
            'payments' => $this->whenLoaded('payments'),
        ];
    }
}
