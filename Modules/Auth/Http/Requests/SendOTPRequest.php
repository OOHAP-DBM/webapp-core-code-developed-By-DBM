<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendOTPRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'identifier' => ['required', 'string'], // email or phone
        ];
    }

    public function messages(): array
    {
        return [
            'identifier.required' => 'Please provide your email or phone number',
        ];
    }
}

