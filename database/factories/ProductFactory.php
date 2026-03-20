<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sku' => strtoupper(fake()->unique()->bothify('??###')),
            'name' => fake()->word(),
            'description' => fake()->optional()->sentence(),
            'price' => fake()->randomFloat(2, 1, 1000),
            'currency' => fake()->randomElement(['USD', 'GBP', 'EUR']),
            'quantity' => fake()->numberBetween(0, 100),
            'is_test' => true,
            'meta' => [],
            'created_by' => User::inRandomOrder()->first()?->id,
        ];
    }
}
