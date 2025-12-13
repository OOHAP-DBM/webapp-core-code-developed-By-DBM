<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * PROMPT 101: Booking Overlap Validation Request
 * 
 * Validates request for checking booking date overlaps
 */
class CheckBookingOverlapRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'hoarding_id' => [
                'required',
                'integer',
                Rule::exists('hoardings', 'id')->where('status', 'active'),
            ],
            'start_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
            'end_date' => [
                'required',
                'date',
                'after:start_date',
            ],
            'exclude_booking_id' => [
                'nullable',
                'integer',
                'exists:bookings,id',
            ],
            'include_grace_period' => [
                'nullable',
                'boolean',
            ],
            'detailed' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'hoarding_id.required' => 'Hoarding ID is required',
            'hoarding_id.exists' => 'Invalid hoarding or hoarding not active',
            'start_date.required' => 'Start date is required',
            'start_date.after_or_equal' => 'Start date cannot be in the past',
            'end_date.required' => 'End date is required',
            'end_date.after' => 'End date must be after start date',
            'exclude_booking_id.exists' => 'Invalid booking ID to exclude',
        ];
    }

    /**
     * Get validated data with defaults
     */
    public function validatedWithDefaults(): array
    {
        $validated = $this->validated();

        return array_merge([
            'exclude_booking_id' => null,
            'include_grace_period' => true,
            'detailed' => false,
        ], $validated);
    }
}
