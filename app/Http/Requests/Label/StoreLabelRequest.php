<?php

namespace App\Http\Requests\Label;

use App\Models\Label;
use Illuminate\Foundation\Http\FormRequest;

class StoreLabelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Label::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'color' => ['required', 'string', 'max:30'],
        ];
    }
}
