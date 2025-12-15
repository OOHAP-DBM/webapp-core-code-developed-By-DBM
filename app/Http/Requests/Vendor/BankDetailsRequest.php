<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class BankDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bank_name' => 'required|string|max:255',
            'account_holder_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:20|regex:/^[0-9]+$/',
            'ifsc_code' => 'required|string|size:11|regex:/^[A-Z]{4}0[A-Z0-9]{6}$/',
            'branch_name' => 'required|string|max:255',
            'account_type' => 'required|in:savings,current',
        ];
    }

    public function messages(): array
    {
        return [
            'account_number.regex' => 'Account number must contain only digits.',
            'ifsc_code.regex' => 'Please enter a valid IFSC code (11 characters, e.g., SBIN0001234).',
        ];
    }
}
