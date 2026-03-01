<?php

namespace Database\Factories;

use App\Models\Learning;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Learning>
 */
class LearningFactory extends Factory
{
    protected $model = Learning::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->optional()->paragraph(),
            'date' => $this->faker->optional()->date(),
            'created_by' => User::factory(),
            'updated_by' => null,
            'deleted_by' => null,
            'meta' => null,
        ];
    }

    /**
     * Indicate that the learning is completed.
     *
     * @param User|null $user
     
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function completed(?User $user = null)
    {
        return $this->state(function () use ($user) {
            return [
                'is_completed' => true,
                'completed_at' => now(),
                'completed_by' => $user?->id,
            ];
        });
    }
}
