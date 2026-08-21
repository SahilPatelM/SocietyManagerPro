<?php

namespace App\Http\Requests\Member;

use Illuminate\Foundation\Http\FormRequest;

class StoreMemberRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|unique:users,mobile|max:15',
            'alternate_mobile' => 'nullable|string|max:15',
            'email' => 'nullable|email',
            'house_id' => 'nullable|exists:houses,id',
            'block_wing' => 'nullable|string',
            'address' => 'nullable|string',
            'password' => 'nullable|string|min:6',
            'status' => 'nullable|in:active,inactive',
            'society_id' => 'required|exists:societies,id',
        ];
    }
}
