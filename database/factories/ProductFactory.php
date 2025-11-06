<?php

namespace Database\Factories;

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
            'sku' => strtoupper($this->faker->unique()->bothify('??###')),
            'name' => $this->faker->word(),
            'description' => $this->faker->optional()->sentence(),
            'price' => $this->faker->randomFloat(2, 1, 1000),
            'currency' => 'USD',
            'quantity' => $this->faker->numberBetween(0, 100),
            'meta' => [],
        ];
    }
}
