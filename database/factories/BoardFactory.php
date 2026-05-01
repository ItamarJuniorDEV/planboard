<?php

namespace Database\Factories;

use App\Models\Board;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Board>
 */
class BoardFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'project_id' => Project::factory(),
            'name' => fake()->randomElement(['Backlog', 'Sprint atual', 'Roadmap', 'Bugs', 'Ideias']),
            'status' => 'active',
        ];
    }

    public function archived(): static
    {
        return $this->state(fn () => ['status' => 'archived']);
    }
}
