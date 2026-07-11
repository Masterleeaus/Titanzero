<?php

namespace Modules\TitanZero\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TitanZeroActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'action' => 'required|string|in:explain_page,fill_form,check_missing,generate_notes',
            'input'  => 'nullable|string|max:2000',
            'context'=> 'nullable|array',
        ];
    }
}
