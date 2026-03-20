<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
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
            'contact' => Contact::class,
            'deal' => Deal::class,
            'user' => User::class,
        ];

        $alias = fake()->randomElement(array_keys($models));
        $modelClass = $models[$alias];

        $taskable = $modelClass::inRandomOrder()->first();

        if (! $taskable) {
            return [
                'title' => null,
                'priority' => null,
                'status' => null,
                'due_at' => null,
                'assigned_to' => null,
                'taskable_type' => $alias,
                'taskable_id' => null,
                'is_test' => true,
                'meta' => [],
                'created_by' => null,
            ];
        }

        return [
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'priority' => 'medium',
            'status' => 'pending',
            'due_at' => fake()->optional()->dateTimeBetween('now', '+30 days'),
            'assigned_to' => User::inRandomOrder()->first()?->id,
            'taskable_type' => $alias,
            'taskable_id' => $taskable->id,
            'is_test' => true,
            'meta' => [],
            'created_by' => User::inRandomOrder()->first()?->id,
        ];
    }
}
