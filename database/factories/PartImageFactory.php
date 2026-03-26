<?php

namespace Database\Factories;

use App\Models\Part;
use App\Models\PartImage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PartImage>
 */
class PartImageFactory extends Factory
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
            'path' => 'parts/' . $this->faker->uuid() . '.jpg',
            'alt' => $this->faker->words(3, true),
            'is_primary' => false,
            'sort_order' => $this->faker->numberBetween(0, 10),
            'is_test' => true,
            'created_by' => User::inRandomOrder()->first()?->id ?? User::factory()->create()->id,
            'created_at' => now(),
        ];
    }

    public function primary(): static
    {
        return $this->state(fn() => ['is_primary' => true]);
    }
}
