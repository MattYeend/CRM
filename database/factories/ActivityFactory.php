<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subject = Company::factory()->create();

        return [
            'user_id' => User::factory(),
            'type' => 'generic',
            'subject_type' => $subject::class,
            'subject_id' => $subject->id,
            'description' => fake()->optional()->sentence(),
            'is_test' => true,
            'meta' => [],
        ];
    }
}
