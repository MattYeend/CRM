<?php

namespace Database\Factories;

use App\Models\Attachment;
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
        return [
            'filename' => fake()->word() . '.txt',
            'disk' => 'public',
            'path' => 'attachments/' . fake()->uuid() . '/file.txt',
            'uploaded_by' => User::factory(),
            'size' => fake()->numberBetween(100, 1000000),
            'mime' => 'text/plain'
        ];
    }
}
