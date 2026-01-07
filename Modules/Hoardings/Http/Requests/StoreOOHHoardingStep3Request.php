<?php

namespace Modules\Hoardings\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOOHHoardingStep3Request extends FormRequest
{
    public function authorize()
    {
        return auth('vendor')->check();
    }

    public function rules()
    {
        return [
            'offer_name' => 'required|array',
            'offer_name.*' => 'required|string|max:255',
            'offer_duration' => 'required|array',
            'offer_duration.*' => 'required|integer|min:1',
            'offer_unit' => 'required|array',
            'offer_unit.*' => 'required|string|in:weeks,months',
            'offer_discount' => 'nullable|array',
            'offer_discount.*' => 'nullable|numeric|min:0',
            'offer_start_date' => 'nullable|array',
            'offer_start_date.*' => 'nullable|date',
            'offer_end_date' => 'nullable|array',
            'offer_end_date.*' => 'nullable|date',
            'offer_services' => 'nullable|array',
            'offer_services.*' => 'nullable|array',
            'offer_services.*.*' => 'string',
            // Weekly prices
            'weekly_price_1' => 'nullable|numeric|min:0',
            'weekly_price_2' => 'nullable|numeric|min:0',
            'weekly_price_3' => 'nullable|numeric|min:0',
        ];
    }
}
