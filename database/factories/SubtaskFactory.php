<?php

namespace Database\Factories;

use App\Models\Subtask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subtask>
 */
class SubtaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'task_id' => Task::factory(),
            'title' => rtrim(fake()->sentence(3), '.'),
            'done' => false,
        ];
    }

    public function done(): static
    {
        return $this->state(fn () => ['done' => true]);
    }
}
