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
            'name' => $this->faker->sentence(3),
            'email' => $this->faker->optional()->safeEmail(),
            'phone' => $this->faker->optional()->phoneNumber(),
            'source' => $this->faker->randomElement(['website', 'email', 'phone', 'referral', 'social_media']),
            'assigned_to' => null,
            'assigned_at' => null,
            'owner_id' => null,
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
        return $this->afterCreating(function (Lead $lead) {
            if (!$lead->owner_id) {
                $lead->owner()->associate(User::factory()->create());
            }
            if(!$lead->assigned_to) {
                $lead->assigned_to = User::factory()->create()->id;
            }
            $lead->save();
        });
    }
}
