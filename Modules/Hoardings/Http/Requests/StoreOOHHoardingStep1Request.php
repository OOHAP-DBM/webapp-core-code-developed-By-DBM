<?php

namespace Modules\Hoardings\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOOHHoardingStep1Request extends FormRequest
{
    public function authorize()
    {
        // Add vendor authorization logic if needed
        return true;
    }

    public function rules()
    {
        return [
            'category' => 'required|string|max:100',
            'width' => 'required|numeric|min:0.1',
            'height' => 'required|numeric|min:0.1',
            'measurement_unit' => 'required|in:sqft,sqm',
            'address' => 'required|string|max:255',
            'pincode' => 'required|string|max:20',
            'locality' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'base_monthly_price' => 'required|numeric|min:1',
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'monthly_price' => [
                'nullable',
                'numeric',
                'lt:base_monthly_price',
            ],
            'media' => 'required|array',
            'media.*' => 'file|mimes:jpg,jpeg,png,webp|mimetypes:image/jpeg,image/png,image/jpg,image/webp|max:5120',
        ];
    }

    public function messages()
    {
        return [
            'monthly_price.lt' => 'Offer price must be less than the base monthly price.',
            'media.required' => 'At least one image is required.',
            'media.*.mimes' => 'Only JPG, JPEG, PNG, and WEBP images are allowed.',
            'media.*.max' => 'Each image must not exceed 5MB.',
        ];
    }
}
