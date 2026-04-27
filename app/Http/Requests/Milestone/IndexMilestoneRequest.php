<?php

namespace App\Http\Requests\Milestone;

use Illuminate\Foundation\Http\FormRequest;

class IndexMilestoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['integer', 'nullable', 'min:1', 'max:20'],
            'search' => ['nullable', 'string'],
            'due_from' => ['nullable', 'date'],
            'due_to' => ['nullable', 'date'],
            'order_by' => ['nullable', 'string', 'in:created_at,due_date,title'],
            'direction' => ['nullable', 'string', 'in:asc,desc'],
        ];
    }
}
