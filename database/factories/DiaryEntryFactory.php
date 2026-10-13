<?php

namespace Database\Factories;

use App\Models\DiaryEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DiaryEntry>
 */
class DiaryEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'entry_date' => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'title' => $this->faker->sentence(4),
            'content' => $this->faker->paragraph(),
            'insights' => $this->faker->optional()->sentence(),
        ];
    }
}
