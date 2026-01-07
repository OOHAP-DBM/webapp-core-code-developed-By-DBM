<?php

namespace Modules\Hoardings\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOOHHoardingStep2Request extends FormRequest
{
    public function authorize()
    {
        // Only allow if authenticated as vendor
        return auth('vendor')->check();
    }

    public function rules()
    {
        return [
            'nagar_nigam_approved' => 'required|boolean',
            'permit_number' => 'nullable|string|max:255',
            'permit_valid_till' => 'nullable|date',
            'expected_footfall' => 'nullable|integer|min:0',
            'expected_eyeball' => 'nullable|integer|min:0',
            'audience_type' => 'nullable|array',
            'audience_type.*' => 'string',
            'blocked_dates_json' => 'nullable|string',
            'needs_grace_period' => 'nullable|in:0,1',
            'grace_period_days' => 'nullable|integer|min:0',
            'visibility_type' => 'nullable|string',
            'visibility_start' => 'nullable|date',
            'visibility_end' => 'nullable|date',
            'facing_direction' => 'nullable|string',
            'road_type' => 'nullable|string',
            'traffic_type' => 'nullable|string',
            'visible_from' => 'nullable|array',
            'visible_from.*' => 'string',
            'located_at' => 'nullable|array',
            'located_at.*' => 'string',
            'brand_logos' => 'nullable|array',
            'brand_logos.*' => 'file|mimes:jpg,jpeg,png,webp|max:5120',
        ];
    }

    public function messages()
    {
        return [
            'nagar_nigam_approved.required' => 'Nagar Nigam approval is required.',
        ];
    }
}
