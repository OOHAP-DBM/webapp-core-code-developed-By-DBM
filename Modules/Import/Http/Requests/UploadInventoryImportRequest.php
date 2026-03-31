<?php

namespace Modules\Import\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadInventoryImportRequest extends FormRequest
{
    /**
     * Normalize legacy field names before validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
         // Debug: Log the detected MIME type for ppt file
            if ($this->hasFile('ppt')) {
                \Log::info('PPT MIME: ' . $this->file('ppt')->getMimeType());
            }
        if (!$this->hasFile('excel') && $this->hasFile('file')) {
            $this->files->set('excel', $this->file('file'));
        }

        if (!$this->hasFile('ppt') && $this->hasFile('ppt_file')) {
            $this->files->set('ppt', $this->file('ppt_file'));
        }

        if ($this->filled('media_type')) {
            $this->merge([
                'media_type' => strtolower((string) $this->input('media_type')),
            ]);
        }
    }

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
                'mimetypes:application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/octet-stream',
                'max:40960', // 40MB in KB
            ],
            'media_type' => [
                'required',
                'string',
                'in:ooh,dooh',
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
            'ppt.mimes' => 'PowerPoint file must be a valid PPT or PPTX file',
            'ppt.mimetypes' => 'PowerPoint file must be a valid PPT or PPTX file',
             'ppt.max' => 'PowerPoint file must not exceed 40MB', 
            'media_type.required' => 'Media type is required',
            'media_type.string' => 'Media type must be a string',
            'media_type.in' => 'Media type must be either OOH or DOOH',
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
