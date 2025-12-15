<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class KYCDocumentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pan_card_document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'gst_certificate' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'company_registration_certificate' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'address_proof' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'cancelled_cheque' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'owner_id_proof' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'other_documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            '*.required' => 'This document is required.',
            '*.file' => 'Please upload a valid file.',
            '*.mimes' => 'File must be PDF, JPG, JPEG, or PNG.',
            '*.max' => 'File size must not exceed 5MB.',
        ];
    }
}
