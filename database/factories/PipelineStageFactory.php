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
     * Configure the factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (PipelineStage $stage) {
            if (!$stage->pipeline_id) {
                $pipeline = Pipeline::factory()->create();
                $stage->pipeline()->associate($pipeline);
                $stage->save();
            }
        });
    }
}
