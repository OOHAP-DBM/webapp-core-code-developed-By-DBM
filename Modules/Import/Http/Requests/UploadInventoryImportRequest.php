<?php

namespace Modules\Import\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadInventoryImportRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'excel' => [
                'required',
                'file',
                'mimes:xlsx',
                'max:20480', // 20MB in KB
            ],
            'ppt' => [
                'required',
                'file',
                'mimes:pptx',
                'max:51200', // 50MB in KB
            ],
            'media_type' => [
                'required',
                'string',
                'max:50',
            ],
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
            'excel.required' => 'Excel file is required',
            'excel.file' => 'Excel must be a valid file',
            'excel.mimes' => 'Excel file must be in XLSX format',
            'excel.max' => 'Excel file must not exceed 20MB',
            'ppt.required' => 'PowerPoint file is required',
            'ppt.file' => 'PowerPoint must be a valid file',
            'ppt.mimes' => 'PowerPoint file must be in PPTX format',
            'ppt.max' => 'PowerPoint file must not exceed 50MB',
            'media_type.required' => 'Media type is required',
            'media_type.string' => 'Media type must be a string',
            'media_type.max' => 'Media type must not exceed 50 characters',
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

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'excel' => 'excel file',
            'ppt' => 'PowerPoint file',
            'media_type' => 'media type',
        ];
    }
}
