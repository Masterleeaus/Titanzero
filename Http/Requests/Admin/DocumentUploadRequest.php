<?php

namespace Modules\TitanZero\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class DocumentUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Pass 4: keep simple; tighten via titanzero.admin in Pass 8
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable','string','max:191'],
            'pdf' => ['required','file','mimes:pdf','max:51200'], // 50MB
        ];
    }
}
