<?php

namespace Database\Factories;

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
        $taskable = User::factory()->create();

        return [
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'priority' => 'medium',
            'status' => 'pending',
            'due_at' => fake()->optional()->dateTimeBetween('now', '+30 days'),
            'assigned_to' => User::inRandomOrder()->first()?->id,
            'taskable_type' => $taskable::class,
            'taskable_id' => $taskable->id,
            'is_test' => true,
            'meta' => [],
            'created_by' => User::inRandomOrder()->first()?->id,
        ];
    }
}
