<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class InvalidatesProjectStats
{
    public function saved(Model $model): void
    {
        $this->invalidate($model);
    }

    public function deleted(Model $model): void
    {
        $this->invalidate($model);
    }

    private function invalidate(Model $model): void
    {
        $projectId = $model->project_id ?? $model->task?->project_id ?? null;

        if ($projectId === null) {
            return;
        }

        Cache::forget("project:{$projectId}:stats");
    }
}
