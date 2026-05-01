<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    public function definition(): array
    {
        $priorities = ['low', 'medium', 'high', 'urgent'];
        $statuses = ['todo', 'doing', 'done'];

        return [
            'user_id' => User::factory(),
            'project_id' => Project::factory(),
            'column_id' => null,
            'title' => rtrim(fake()->sentence(4), '.'),
            'description' => fake()->paragraph(),
            'priority' => fake()->randomElement($priorities),
            'status' => fake()->randomElement($statuses),
        ];
    }

    public function todo(): static
    {
        return $this->state(fn () => ['status' => 'todo']);
    }

    public function doing(): static
    {
        return $this->state(fn () => ['status' => 'doing']);
    }

    public function done(): static
    {
        return $this->state(fn () => ['status' => 'done']);
    }

    public function urgent(): static
    {
        return $this->state(fn () => ['priority' => 'urgent']);
    }
}
