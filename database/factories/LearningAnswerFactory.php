<?php

namespace Database\Factories;

use App\Models\LearningQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LearningAnswer>
 */
class LearningAnswerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question_id' => LearningQuestion::factory(),
            'answer' => $this->faker->sentence(),
            'is_correct' => false,
        ];
    }

    /**
     * Mark the answer as correct.
     */
    public function correct(): static
    {
        return $this->state(['is_correct' => true]);
    }
}
