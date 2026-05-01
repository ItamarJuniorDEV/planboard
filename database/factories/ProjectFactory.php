<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    public function definition(): array
    {
        $statuses = ['draft', 'planning', 'active', 'on_hold', 'completed', 'cancelled'];

        return [
            'user_id' => User::factory(),
            'title' => rtrim(fake()->sentence(3), '.'),
            'description' => fake()->paragraph(),
            'budget' => fake()->randomFloat(2, 1000, 50000),
            'status' => fake()->randomElement($statuses),
            'deadline' => now()->addDays(fake()->numberBetween(7, 90))->format('Y-m-d'),
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => ['status' => 'active']);
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => 'draft']);
    }

    public function completed(): static
    {
        return $this->state(fn () => ['status' => 'completed']);
    }
}
