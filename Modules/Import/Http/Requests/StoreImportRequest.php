<?php

namespace Modules\Import\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreImportRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $supportedFormats = config('import.supported_formats', ['csv', 'xlsx', 'xls', 'json']);
        $maxFileSize = config('import.max_file_size', 10 * 1024 * 1024);

        return [
            'file' => [
                'required',
                'file',
                sprintf('max:%d', $maxFileSize),
                sprintf('mimes:%s', implode(',', $supportedFormats)),
            ],
            'import_type' => ['required', 'string'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'file.required' => 'The file field is required.',
            'file.file' => 'The file must be a valid file.',
            'file.max' => 'The file size must not exceed ' . config('import.max_file_size', 10 * 1024 * 1024) . ' bytes.',
            'file.mimes' => 'The file must be one of: ' . implode(', ', config('import.supported_formats', [])),
            'import_type.required' => 'The import type is required.',
            'import_type.string' => 'The import type must be a string.',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }
}
