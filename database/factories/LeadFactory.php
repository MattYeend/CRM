<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lead>
 */
class LeadFactory extends Factory
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
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->optional()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'source' => fake()->randomElement(['website', 'email', 'phone', 'referral', 'social_media']),
            'owner_id' => User::inRandomOrder()->value('id'),
            'assigned_to' => User::inRandomOrder()->value('id'),
            'assigned_at' => now(),
            'is_test' => true,
            'meta' => [],
            'created_by' => User::inRandomOrder()->value('id'),
        ];
    }

    /**
     * Indicate that the lead is unassigned.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unassigned(): static
    {
        return $this->state([
            'assigned_to' => null,
            'assigned_at' => null,
        ]);
    }
}
