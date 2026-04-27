<?php

namespace App\Http\Requests\Label;

use App\Models\Label;
use Illuminate\Foundation\Http\FormRequest;

class IndexLabelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Label::class);
    }

    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:30'],
            'search' => ['nullable', 'string'],
        ];
    }
}
