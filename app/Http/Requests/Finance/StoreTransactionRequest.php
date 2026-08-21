<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type' => 'required|in:income,expense',
            'category' => 'required|string|max:100',
            'subcategory' => 'nullable|string|max:100',
            'amount' => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'payment_method' => 'nullable|string|max:50',
            'reference_number' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'house_id' => 'nullable|exists:houses,id',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:5120',
        ];
    }
}
