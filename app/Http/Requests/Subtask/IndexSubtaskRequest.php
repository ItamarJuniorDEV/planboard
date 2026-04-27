<?php

namespace App\Http\Requests\Subtask;

use Illuminate\Foundation\Http\FormRequest;

class IndexSubtaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['integer', 'nullable', 'min:1', 'max:50'],
        ];
    }
}
