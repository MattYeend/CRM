<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Deal;
use App\Models\Note;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Note>
 */
class NoteFactory extends Factory
{
    protected $model = Note::class;

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

        $notable = $modelClass::inRandomOrder()->first();

        if (! $notable) {
            return [
                'user_id' => null,
                'notable_type' => $alias,
                'notable_id' => null,
                'body' => null,
                'is_test' => true,
                'meta' => [],
                'created_by' => null,
            ];
        }

        return [
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory()->create()->id,
            'notable_type' => $alias,
            'notable_id' => $notable->id,
            'body' => fake()->optional()->paragraph(),
            'is_test' => true,
            'meta' => [],
            'created_by' => User::inRandomOrder()->first()?->id ?? User::factory()->create()->id,
        ];
    }
}