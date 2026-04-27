<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class IndexTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['integer', 'nullable', 'min:1', 'max:50'],
            'priority' => ['nullable', 'string', 'in:low,medium,high,urgent'],
            'search' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:todo,doing,done'],
            'order_by' => ['nullable', 'string', 'in:created_at,priority,status,title'],
            'direction' => ['nullable', 'string', 'in:asc,desc'],
        ];
    }
}
