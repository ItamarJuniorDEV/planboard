<?php

namespace App\Http\Requests\Project;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class IndexProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Project::class);
    }

    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'status' => ['nullable', 'string', 'in:draft,planning,active,on_hold,completed,cancelled'],
            'search' => ['nullable', 'string'],
            'deadline_from' => ['nullable', 'date'],
            'deadline_to' => ['nullable', 'date'],
            'order_by' => ['nullable', 'string', 'in:created_at,title,deadline,budget'],
            'direction' => ['nullable', 'string', 'in:asc,desc'],
        ];
    }
}
