<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'budget',
        'status',
        'deadline',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'deadline' => 'date',
    ];

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function boards()
    {
        return $this->hasMany(Board::class);
    }

    public function milestones()
    {
        return $this->hasMany(Milestone::class);
    }

    public function labels()
    {
        return $this->hasMany(Label::class);
    }
}
