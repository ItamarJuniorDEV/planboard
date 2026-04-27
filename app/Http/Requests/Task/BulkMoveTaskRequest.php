<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class BulkMoveTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'task_ids' => ['required', 'array', 'min:1'],
            'task_ids.*' => ['required', 'integer', 'distinct'],
            'column_id' => ['required', 'integer'],
        ];
    }
}
