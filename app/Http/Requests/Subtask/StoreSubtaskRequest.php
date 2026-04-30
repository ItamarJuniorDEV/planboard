<?php

namespace App\Http\Requests\Subtask;

use App\Models\Subtask;
use Illuminate\Foundation\Http\FormRequest;

class StoreSubtaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Subtask::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'done' => ['required', 'boolean'],
        ];
    }
}
