<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Deal;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attachment>
 */
class AttachmentFactory extends Factory
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

        $attachable = $modelClass::inRandomOrder()->first();

        if(! $attachable){
            return [
                'filename' => fake()->word() . '.txt',
                'disk' => 'public',
                'path' => 'attachments/' . fake()->uuid() . '/file.txt',
                'uploaded_by' => User::inRandomOrder()->first()?->id,
                'size' => fake()->numberBetween(100, 1000000),
                'mime' => 'text/plain',
                'attachable_id' => 1,
                'attachable_type' => 'Document',
                'is_test' => true,
                'meta' => [],
                'created_by' => User::inRandomOrder()->first()?->id,
            ];
        }

        return [
            'filename' => fake()->word() . '.txt',
            'disk' => 'public',
            'path' => 'attachments/' . fake()->uuid() . '/file.txt',
            'uploaded_by' => User::inRandomOrder()->first()?->id,
            'size' => fake()->numberBetween(100, 1000000),
            'mime' => 'text/plain',
            'attachable_id' => $attachable->id,
            'attachable_type' => $alias,
            'is_test' => true,
            'meta' => [],
            'created_by' => User::inRandomOrder()->first()?->id,
        ];
    }
}
