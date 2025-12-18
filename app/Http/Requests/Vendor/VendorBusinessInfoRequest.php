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
                'required',
                'string',
                'max:15',
                Rule::unique('vendor_profiles', 'gstin')->ignore(optional($this->user()->vendorProfile)->id),
            ],
            'business_type' => 'required|string|max:50',
            'business_name' => 'required|string|max:255',
            'registered_address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => 'required|string|max:10',

            // Bank Info
            'bank_name' => 'required|string|max:100',
            'account_number' => 'required|string|max:30',
            'ifsc_code' => 'required|string|max:20',
            'account_holder_name' => 'required|string|max:100',

            // PAN
            'pan_number' => [
                'required',
                'string',
                'max:10',
                Rule::unique('vendor_profiles', 'pan')->ignore(optional($this->user()->vendorProfile)->id),
            ],
            'pan_card_document' => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:5120', // ≤ 5MB
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
