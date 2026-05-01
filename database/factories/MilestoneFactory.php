<?php

namespace Database\Factories;

use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Milestone>
 */
class MilestoneFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'project_id' => Project::factory(),
            'title' => rtrim(fake()->sentence(3), '.'),
            'due_date' => now()->addDays(fake()->numberBetween(7, 120))->format('Y-m-d'),
        ];
    }

    public function overdue(): static
    {
        return $this->state(fn () => [
            'due_date' => now()->subDays(fake()->numberBetween(1, 30))->format('Y-m-d'),
        ]);
    }
}
