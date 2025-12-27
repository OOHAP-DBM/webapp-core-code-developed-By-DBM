<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VendorBusinessInfoRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            // Business Info
            'gstin' => [
                'nullable',
                'string',
                'max:15',
                Rule::unique('vendor_profiles', 'gstin')->ignore(optional($this->user()->vendorProfile)->id),
            ],
            'business_type' => 'nullable|string|max:50',
            'business_name' => 'nullable|string|max:255',
            'registered_address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',

            // Bank Info
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:30',
            'ifsc_code' => 'nullable|string|max:20',
            'account_holder_name' => 'nullable|string|max:100',

            // PAN
            'pan_number' => [
                'nullable',
                'string',
                'max:10',
                Rule::unique('vendor_profiles', 'pan')->ignore(optional($this->user()->vendorProfile)->id),
            ],
            'pan_card_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:5120', // ≤ 5MB
        ];
    }

    public function messages()
    {
        return [
            'pan_card_document.max' => 'PAN document must not exceed 5MB.',
            'pan_card_document.mimes' => 'PAN document must be a valid file type (pdf, jpg, jpeg, png, webp).',
        ];
    }
}
