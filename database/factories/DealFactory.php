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
            'title' => fake()->sentence(3),
            'company_id' => Company::factory(),
            'contact_id' => Contact::factory(),
            'owner_id' => User::factory(),
            'pipeline_id' => Pipeline::factory(),
            'value' => fake()->randomFloat(2, 100, 10000),
            'currency' => 'USD',
            'close_date' => fake()->optional()->dateTimeBetween('now', '+1 year'),
            'status' => 'open',
            'meta' => [],
        ];
    }

    /**
     * Configure the factory.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function configure()
    {
        return $this->afterMaking(function (Deal $deal) {
            if (!$deal->stage_id && $deal->pipeline_id) {
                $stage = PipelineStage::factory()
                    ->for($deal->pipeline)
                    ->create();
    
                $deal->stage_id = $stage->id;
            }
        });
    }
}
