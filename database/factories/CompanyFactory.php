<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'industry' => fake()->companySuffix(),
            'website' => fake()->optional()->url(),
            'phone' => fake()->optional()->phoneNumber(),
            'address' => fake()->optional()->streetAddress(),
            'city' => fake()->optional()->city(),
            'region' => fake()->optional()->state(),
            'postal_code' => fake()->optional()->postcode(),
            'country' => fake()->country(),
            'is_test' => true,
            'meta' => [],
        ];
    }
}
