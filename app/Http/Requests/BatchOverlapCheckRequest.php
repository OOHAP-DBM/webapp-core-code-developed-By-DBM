<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * PROMPT 101: Batch Overlap Validation Request
 * 
 * Validates multiple date ranges at once
 */
class BatchOverlapCheckRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
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
                'exists:hoardings,id',
            ],
            'date_ranges' => [
                'required',
                'array',
                'min:1',
                'max:20', // Limit to 20 ranges per request
            ],
            'date_ranges.*.start' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
            'date_ranges.*.end' => [
                'required',
                'date',
                'after:date_ranges.*.start',
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
            'hoarding_id.exists' => 'Invalid hoarding',
            'date_ranges.required' => 'At least one date range is required',
            'date_ranges.min' => 'At least one date range is required',
            'date_ranges.max' => 'Maximum 20 date ranges allowed per request',
            'date_ranges.*.start.required' => 'Start date is required for each range',
            'date_ranges.*.start.after_or_equal' => 'Start date cannot be in the past',
            'date_ranges.*.end.required' => 'End date is required for each range',
            'date_ranges.*.end.after' => 'End date must be after start date',
        ];
    }
}
