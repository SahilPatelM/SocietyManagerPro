<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'category' => $this->category,
            'amount' => $this->amount,
            'transaction_date' => $this->transaction_date?->format('Y-m-d'),
            'payment_method' => $this->payment_method,
            'description' => $this->description,
            'house' => $this->whenLoaded('house'),
            'attachments' => $this->whenLoaded('attachments'),
        ];
    }
}
