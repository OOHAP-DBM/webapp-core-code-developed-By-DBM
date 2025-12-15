<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class BusinessInfoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'year_established' => 'required|integer|min:1900|max:' . date('Y'),
            'total_hoardings' => 'nullable|integer|min:0',
            'service_cities' => 'required|array|min:1',
            'service_cities.*' => 'string|max:100',
            'hoarding_types' => 'required|array|min:1',
            'hoarding_types.*' => 'string|in:billboard,unipole,gantry,pole_kiosk,bus_shelter,metro_pillar,bridge,skywalk,other',
            'business_description' => 'nullable|string|max:1000',
            'contact_person_name' => 'required|string|max:255',
            'contact_person_designation' => 'nullable|string|max:100',
            'contact_person_phone' => 'required|string|max:15|regex:/^[0-9]{10,15}$/',
            'contact_person_email' => 'required|email|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'year_established.max' => 'Year established cannot be in the future.',
            'service_cities.required' => 'Please select at least one service city.',
            'hoarding_types.required' => 'Please select at least one hoarding type.',
            'contact_person_phone.regex' => 'Please enter a valid phone number.',
        ];
    }
}
