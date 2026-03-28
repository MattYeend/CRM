<?php

namespace Database\Factories;

use App\Models\Part;
use App\Models\PartSerialNumber;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PartSerialNumber>
 */
class PartSerialNumberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'part_id' => Part::factory(),
            'serial_number' => strtoupper(fake()->unique()->bothify('SN-????-#####')),
            'status' => fake()->randomElement(PartSerialNumber::STATUSES),
            'batch_number' => strtoupper(fake()->optional()->bothify('BATCH-####')),
            'manufactured_at' => fake()->optional()->dateTimeBetween('-2 years', 'now'),
            'expires_at' => fake()->optional()->dateTimeBetween('now', '+3 years'),
            'is_test' => true,
            'meta' => [],
            'created_by' => User::inRandomOrder()->first()?->id,
        ];
    }

    public function sold(): static
    {
        return $this->state(fn() => ['status' => 'sold']);
    }

    public function scrapped(): static
    {
        return $this->state(fn() => ['status' => 'scrapped']);
    }
}
