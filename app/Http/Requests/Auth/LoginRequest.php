<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'mobile' => 'required|string|min:10|max:15',
            'password' => 'required|string|min:6',
        ];
    }
}
