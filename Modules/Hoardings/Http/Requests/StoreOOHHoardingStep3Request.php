<?php

namespace Modules\Hoardings\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreOOHHoardingStep3Request extends FormRequest
{
    public function authorize()
    {
        return Auth::check();
    }

    public function rules()
    {
        return [
            'offer_name' => 'nullable|array',
            'offer_name.*' => 'nullable|string|max:255',
            'offer_duration' => 'nullable|array',
            'offer_duration.*' => 'nullable|integer|min:1',
            'offer_unit' => 'nullable|array',
            'offer_unit.*' => 'nullable|string|in:weeks,months',
            'offer_discount' => 'nullable|array',
            'offer_discount.*' => 'nullable|numeric|min:0',
            'offer_start_date' => 'nullable|array',
            'offer_start_date.*' => 'nullable|date',
            'offer_end_date' => 'nullable|array',
            'offer_end_date.*' => 'nullable|date',
            'offer_services' => 'nullable|array',
            'offer_services.*' => 'nullable|array',
            'offer_services.*.*' => 'nullable|string',
            // Weekly prices
            'weekly_price_1' => 'nullable|numeric|min:0',
            'weekly_price_2' => 'nullable|numeric|min:0',
            'weekly_price_3' => 'nullable|numeric|min:0',
        ];
    }
}
