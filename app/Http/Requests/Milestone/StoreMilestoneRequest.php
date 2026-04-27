<?php

namespace App\Http\Requests\Milestone;

use App\Models\Milestone;
use Illuminate\Foundation\Http\FormRequest;

class StoreMilestoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Milestone::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'due_date' => ['nullable', 'date'],
        ];
    }
}
