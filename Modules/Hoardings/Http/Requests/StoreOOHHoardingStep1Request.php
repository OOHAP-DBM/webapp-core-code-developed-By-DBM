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
         $oohId = $this->input('ooh_id');
        return [
            'category' => 'required|string|max:100',
            'width' => 'required|numeric|min:0.1',
            'height' => 'required|numeric|min:0.1',
            'measurement_unit' => 'required|in:sqft,sqm',
            'address' => 'required|string|max:255',
            'pincode' => 'required|string|max:20',
            'locality' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'base_monthly_price' => 'required|numeric|min:1',
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'deleted_media_ids' => 'nullable',
            'monthly_price' => [
                'nullable',
                'numeric',
                'lte:base_monthly_price',
            ],
            'media'=> $oohId ? 'nullable|array' : 'required|array|min:1',
           'media.*' => [
                'file',
                'max:10240',
                function ($attribute, $value, $fail) {
                    $allowed = [
                        'image/jpeg', 'image/jpg', 'image/png', 'image/webp',
                        'video/mp4', 'video/webm',
                    ];
                    if (!in_array($value->getMimeType(), $allowed)) {
                        $fail('Only JPG, PNG, WEBP images and MP4, WEBM videos are allowed.');
                    }
                },
           ],
        ];
    }

    public function messages()
    {
        return [
            'monthly_price.lt' => 'Monthly Discounted Price  must be less than the Monthly Base Price (₹).',
            'media.required' => 'At least one image is required.',
            'media.*.mimes' => 'Only JPG, JPEG, PNG, and WEBP images are allowed.',
            'media.*.max' => 'Each image must not exceed 5MB.',
        ];
    }
}
