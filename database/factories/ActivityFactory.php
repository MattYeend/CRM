<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Relations\Relation;

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
        $models = [
            Company::class,
            Contact::class,
            Deal::class,
            Task::class,
            User::class,
        ];

        $modelClass = fake()->randomElement($models);
        $subject = $modelClass::factory()->create();

        $morphAlias = array_search($modelClass, Relation::morphMap(), true);

        return [
            'user_id' => User::inRandomOrder()->value('id'),
            'type' => 'generic',
            'subject_type' => $morphAlias,
            'subject_id' => $subject->id,
            'description' => fake()->optional()->sentence(),
            'is_test' => true,
            'meta' => [],
            'created_by' => User::inRandomOrder()->value('id'),
        ];
    }
}
