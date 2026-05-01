<?php

namespace Database\Factories;

use App\Models\Label;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Label>
 */
class LabelFactory extends Factory
{
    public function definition(): array
    {
        $names = ['urgent', 'bug', 'feature', 'documentation', 'refactor', 'tech-debt'];
        $colors = ['#e11d48', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6', '#64748b'];

        return [
            'user_id' => User::factory(),
            'project_id' => Project::factory(),
            'name' => fake()->randomElement($names),
            'color' => fake()->randomElement($colors),
        ];
    }
}
