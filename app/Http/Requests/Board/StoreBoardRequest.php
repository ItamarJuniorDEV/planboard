<?php

namespace App\Http\Requests\Board;

use App\Models\Board;
use Illuminate\Foundation\Http\FormRequest;

class StoreBoardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Board::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'status' => ['required', 'string', 'in:active,archived'],
        ];
    }
}
