<?php

namespace Database\Factories;

use App\Models\Deal;
use App\Models\Company;
use App\Models\Contact;
use App\Models\User;
use App\Models\Pipeline;
use App\Models\PipelineStage;
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
            'title' => $this->faker->sentence(3),
            'company_id' => null,
            'contact_id' => null,
            'owner_id' => null,
            'pipeline_id' => null,
            'stage_id' => null,
            'value' => $this->faker->randomFloat(2, 100, 10000),
            'currency' => 'USD',
            'close_date' => $this->faker->optional()->dateTimeBetween('now', '+1 year'),
            'status' => 'open',
            'meta' => [],
        ];
    }

    /**
     * Configure the factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (Deal $deal) {
            if (!$deal->company_id) {
                $deal->company()->associate(Company::factory()->create());
            }
            if (!$deal->contact_id) {
                $deal->contact()->associate(Contact::factory()->create());
            }
            if (!$deal->owner_id) {
                $deal->owner()->associate(User::factory()->create());
            }
            if (!$deal->pipeline_id) {
                $pipeline = Pipeline::factory()->create();
                $deal->pipeline()->associate($pipeline);
            }
            if (!$deal->stage_id) {
                // ensure a stage exists for the pipeline
                $stage = PipelineStage::factory()->for($deal->pipeline)->create();
                $deal->stage()->associate($stage);
            }
            $deal->save();
        });
    }
}
