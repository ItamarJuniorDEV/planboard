<?php

namespace App\Http\Requests\Comment;

use App\Models\Comment;
use Illuminate\Foundation\Http\FormRequest;

class BulkDeleteCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Comment::class);
    }

    public function rules(): array
    {
        return [
            'comment_ids' => ['required', 'array', 'min:1'],
            'comment_ids.*' => ['required', 'integer', 'distinct'],
        ];
    }
}
