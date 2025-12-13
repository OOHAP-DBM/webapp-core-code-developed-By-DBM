<?php

namespace App\Http\Requests;

use App\Models\MaintenanceBlock;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * PROMPT 102: Request validation for updating maintenance blocks
 */
class UpdateMaintenanceBlockRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization handled by middleware and controller
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
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after_or_equal:start_date'],
            'status' => ['sometimes', Rule::in([
                MaintenanceBlock::STATUS_ACTIVE,
                MaintenanceBlock::STATUS_COMPLETED,
                MaintenanceBlock::STATUS_CANCELLED,
            ])],
            'block_type' => ['sometimes', Rule::in([
                MaintenanceBlock::TYPE_MAINTENANCE,
                MaintenanceBlock::TYPE_REPAIR,
                MaintenanceBlock::TYPE_INSPECTION,
                MaintenanceBlock::TYPE_OTHER,
            ])],
            'affected_by' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'end_date.after_or_equal' => 'End date must be after or equal to start date.',
        ];
    }
}
