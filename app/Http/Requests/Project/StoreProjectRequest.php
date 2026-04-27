<?php

namespace App\Http\Requests\Project;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Project::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'budget' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'in:draft,planning,active,on_hold,completed,cancelled'],
            'deadline' => ['nullable', 'date'],
        ];
    }
}
