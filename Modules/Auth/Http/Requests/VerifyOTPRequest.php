<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOTPRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'identifier' => 'required|string',
            'otp'        => 'required|string|min:4|max:6', // Ensure this is exactly like this
        ];
    }

    public function messages(): array
    {
        return [
            'identifier.required' => 'Please provide your email or phone number',
            'otp.required' => 'OTP is required',
            'otp.size' => 'OTP must be 6 digits',
        ];
    }
}
