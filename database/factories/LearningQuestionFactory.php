<?php

namespace Database\Factories;

use App\Models\Learning;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LearningQuestion>
 */
class LearningQuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'learning_id' => Learning::factory(),
            'question' => $this->faker->sentence() . '?',
        ];
    }
}
