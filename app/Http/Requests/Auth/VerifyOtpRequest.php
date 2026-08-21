<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'mobile' => 'required|string|min:10|max:15',
            'otp' => 'required|string|size:6',
        ];
    }
}
