<?php

namespace App\Http\Requests\Board;

use Illuminate\Foundation\Http\FormRequest;

class IndexBoardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:20'],
            'status' => ['nullable', 'string', 'in:active,archived'],
            'search' => ['nullable', 'string'],
        ];
    }
}
