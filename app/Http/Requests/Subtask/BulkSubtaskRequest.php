<?php

namespace App\Http\Requests\Subtask;

use Illuminate\Foundation\Http\FormRequest;

class BulkSubtaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subtask_ids' => ['required', 'array', 'min:1'],
            'subtask_ids.*' => ['required', 'integer', 'distinct'],
        ];
    }
}
