<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\Company;
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
            'name' => fake()->sentence(3),
            'email' => fake()->optional()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'source' => fake()->randomElement(['website', 'email', 'phone', 'referral', 'social_media']),
            'owner_id' => User::factory(),
            'assigned_to' => User::factory(),
            'assigned_at' => now(),
            'meta' => [],
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
