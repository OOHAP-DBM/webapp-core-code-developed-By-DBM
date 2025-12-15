<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class TermsAgreementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'terms_accepted' => 'required|accepted',
            'commission_agreement_accepted' => 'required|accepted',
        ];
    }

    public function messages(): array
    {
        return [
            'terms_accepted.accepted' => 'You must accept the Terms & Conditions to proceed.',
            'commission_agreement_accepted.accepted' => 'You must accept the Commission Agreement to proceed.',
        ];
    }
}
