<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HouseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'house_number' => $this->house_number,
            'status' => $this->status,
            'outstanding_amount' => $this->outstanding_amount,
            'paid_amount' => $this->paid_amount,
            'last_payment_date' => $this->last_payment_date?->format('Y-m-d'),
            'owner' => $this->whenLoaded('owner'),
            'block' => $this->whenLoaded('block'),
        ];
    }
}
