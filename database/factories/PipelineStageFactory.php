<?php

namespace Database\Factories;

use App\Models\PipelineStage;
use App\Models\Pipeline;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PipelineStage>
 */
class PipelineStageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pipeline_id' => null,
            'name' => $this->faker->word(),
            'position' => 0,
            'is_won_stage' => false,
            'is_lost_stage' => false,
        ];
    }

    /**
     * Indicate that the won stage belongs to a pipeline.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function won(): static
    {
        return $this->state([
            'is_won_stage' => true,
            'is_lost_stage' => false,
        ]);
    }

    /**
     * Indicate that the lost stage belongs to a pipeline.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function lost(): static
    {
        return $this->state([
            'is_won_stage' => false,
            'is_lost_stage' => true,
        ]);
    }
}
