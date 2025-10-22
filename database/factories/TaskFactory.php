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
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->optional()->paragraph(),
            'assigned_to' => null,
            'created_by' => null,
            'priority' => 'medium',
            'status' => 'pending',
            'due_at' => $this->faker->optional()->dateTimeBetween('now', '+30 days'),
        ];
    }

    /**
     * Configure the factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (Task $task) {
            if (!$task->assigned_to) {
                $task->assignee()->associate(User::factory()->create());
            }
            if (!$task->created_by) {
                $task->creator()->associate(User::factory()->create());
            }
            $task->save();
        });
    }
}
