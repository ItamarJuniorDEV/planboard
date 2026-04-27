<?php

namespace App\Http\Requests\Comment;

use Illuminate\Foundation\Http\FormRequest;

class BulkDeleteCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'comment_ids' => ['required', 'array', 'min:1'],
            'comment_ids.*' => ['required', 'integer', 'distinct'],
        ];
    }
}
