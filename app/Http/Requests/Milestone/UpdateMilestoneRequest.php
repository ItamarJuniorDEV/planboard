<?php

namespace App\Http\Requests\Milestone;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMilestoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('milestone'));
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'due_date' => ['nullable', 'date'],
        ];
    }
}
