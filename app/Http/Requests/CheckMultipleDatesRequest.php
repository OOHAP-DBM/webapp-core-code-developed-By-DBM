<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

/**
 * PROMPT 104: Validate batch date checking requests
 */
class CheckMultipleDatesRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'dates' => [
                'required',
                'array',
                'min:1',
                'max:100',
            ],
            'dates.*' => [
                'required',
                'date',
                'date_format:Y-m-d',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'dates.required' => 'At least one date is required.',
            'dates.array' => 'Dates must be provided as an array.',
            'dates.min' => 'At least one date is required.',
            'dates.max' => 'Maximum 100 dates can be checked at once.',
            'dates.*.required' => 'All dates are required.',
            'dates.*.date' => 'All dates must be valid dates.',
            'dates.*.date_format' => 'All dates must be in YYYY-MM-DD format.',
        ];
    }
    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->has('dates') && is_array($this->input('dates'))) {
                foreach ($this->input('dates') as $idx => $date) {
                    if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date)) {
                        $validator->errors()->add("dates.$idx", 'Date must be in YYYY-MM-DD format, not DD/MM/YYYY.');
                    }
                }
            }
        });
    }
}
