<?php

namespace App\Http\Requests\Column;

use Illuminate\Foundation\Http\FormRequest;

class IndexColumnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
}
