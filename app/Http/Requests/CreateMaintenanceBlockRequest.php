<?php

namespace App\Http\Requests;

use App\Models\MaintenanceBlock;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * PROMPT 102: Request validation for creating maintenance blocks
 */
class CreateMaintenanceBlockRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization handled by middleware (admin or vendor who owns the hoarding)
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
            'hoarding_id' => ['required', 'integer', 'exists:hoardings,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', Rule::in([
                MaintenanceBlock::STATUS_ACTIVE,
                MaintenanceBlock::STATUS_COMPLETED,
                MaintenanceBlock::STATUS_CANCELLED,
            ])],
            'block_type' => ['nullable', Rule::in([
                MaintenanceBlock::TYPE_MAINTENANCE,
                MaintenanceBlock::TYPE_REPAIR,
                MaintenanceBlock::TYPE_INSPECTION,
                MaintenanceBlock::TYPE_OTHER,
            ])],
            'affected_by' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'force_create' => ['nullable', 'boolean'], // Create even if conflicts exist
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'hoarding_id.required' => 'Please select a hoarding.',
            'hoarding_id.exists' => 'The selected hoarding does not exist.',
            'title.required' => 'Please provide a title for the maintenance block.',
            'start_date.required' => 'Please provide a start date.',
            'start_date.after_or_equal' => 'Start date must be today or in the future.',
            'end_date.required' => 'Please provide an end date.',
            'end_date.after_or_equal' => 'End date must be after or equal to start date.',
        ];
    }
}
