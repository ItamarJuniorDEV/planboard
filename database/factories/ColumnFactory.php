<?php

namespace Database\Factories;

use App\Models\Board;
use App\Models\Column;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Column>
 */
class ColumnFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'board_id' => Board::factory(),
            'name' => fake()->randomElement(['A fazer', 'Em andamento', 'Em revisão', 'Concluído']),
            'position' => fake()->numberBetween(1, 10),
        ];
    }
}
