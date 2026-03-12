<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Label extends Model
{
    protected $fillable = [
        'user_id',
        'project_id',
        'name',
        'color',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
