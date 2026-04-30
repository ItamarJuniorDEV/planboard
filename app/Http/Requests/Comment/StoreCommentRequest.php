<?php

namespace App\Http\Requests\Comment;

use App\Models\Comment;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Comment::class);
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string'],
            'author' => ['required', 'string', 'max:100'],
        ];
    }
}
