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
        $notable = Deal::factory()->create();

        return [
            'user_id' => null,
            'body' => $this->faker->paragraph(),
            'meta' => [],
            'notable_type' => get_class($notable),
            'notable_id' => $notable->id,
        ];
    }

    /**
     * Configure the factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (Note $note) {
            if (!$note->user_id) {
                $note->user()->associate(User::factory()->create());
                $note->save();
            }
        });
    }
}