<?php

namespace App\Http\Requests\Member;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMemberRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'mobile' => 'sometimes|string|max:15',
            'alternate_mobile' => 'nullable|string|max:15',
            'email' => 'nullable|email',
            'house_id' => 'nullable|exists:houses,id',
            'block_wing' => 'nullable|string',
            'address' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ];
    }
}
