<?php

namespace App\Models;

use App\Observers\InvalidatesProjectStats;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(InvalidatesProjectStats::class)]
class Subtask extends Model
{
    protected $fillable = [
        'user_id',
        'task_id',
        'title',
        'done',
    ];

    protected $casts = [
        'done' => 'boolean',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
