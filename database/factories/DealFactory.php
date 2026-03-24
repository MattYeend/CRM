<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\User;
use App\Models\Pipeline;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Deal>
 */
class DealFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'company_id' => Company::inRandomOrder()->first()?->id,
            'owner_id' => User::inRandomOrder()->first()?->id,
            'pipeline_id' => Pipeline::inRandomOrder()->first()?->id,
            'value' => fake()->randomFloat(2, 100, 10000),
            'currency' => fake()->randomElement(['GBP', 'USD', 'EUR']),
            'close_date' => fake()->optional()->dateTimeBetween('now', '+1 year'),
            'status' => 'open',
            'is_test' => true,
            'meta' => [],
            'created_by' => User::inRandomOrder()->first()?->id,
        ];
    }
}
