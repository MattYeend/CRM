<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Deal;
use App\Models\Task;
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
        $models = [
            'company' => Company::class,
            'deal' => Deal::class,
            'task' => Task::class,
            'user' => User::class,
        ];

        $alias = fake()->randomElement(array_keys($models));
        $modelClass = $models[$alias];

        $subject = $modelClass::inRandomOrder()->first();

        if (! $subject) {
            return [
                'user_id' => null,
                'type' => 'generic',
                'subject_type' => $alias,
                'subject_id' => null,
                'description' => null,
                'is_test' => true,
                'meta' => [],
                'created_by' => null,
            ];
        }

        return [
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory()->create()->id,
            'type' => 'generic',
            'subject_type' => $alias,
            'subject_id' => $subject->id,
            'description' => fake()->optional()->sentence(),
            'is_test' => true,
            'meta' => [],
            'created_by' => User::inRandomOrder()->first()?->id ?? User::factory()->create()->id,
        ];
    }
}
