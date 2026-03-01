<?php

namespace Database\Factories;

use App\Models\Deal;
use App\Models\Note;
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
        return [
            'user_id' => User::factory(),
            'body' => fake()->paragraph(),
            'meta' => [],
        ];
    }

    /**
     * Associate the note with a deal.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function forDeal(): static
    {
        return $this->for(Deal::factory(), 'notable');
    }

    /**
     * Associate the note with any model.
     *
     * @param mixed $model
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function forModel($model): static
    {
        return $this->for($model, 'notable');
    }
}