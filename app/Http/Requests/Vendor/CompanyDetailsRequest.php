<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class CompanyDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name' => 'required|string|max:255',
            'company_registration_number' => 'nullable|string|max:100',
            'company_type' => 'required|in:proprietorship,partnership,private_limited,public_limited,llp,other',
            'gstin' => 'required|string|size:15|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
            'pan' => 'required|string|size:10|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
            'registered_address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => 'required|string|size:6|regex:/^[0-9]{6}$/',
            'website' => 'nullable|url|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'gstin.regex' => 'Please enter a valid GSTIN (15 characters).',
            'pan.regex' => 'Please enter a valid PAN (10 characters, e.g., ABCDE1234F).',
            'pincode.regex' => 'Please enter a valid 6-digit pincode.',
        ];
    }
}
