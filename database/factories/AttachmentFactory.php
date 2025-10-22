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
            'filename' => $this->faker->word() . '.txt',
            'disk' => 'public',
            'path' => 'attachments/' . $this->faker->uuid() . '/file.txt',
            'uploaded_by' => null,
            'size' => $this->faker->numberBetween(100, 1000000),
            'mime' => 'text/plain'
        ];
    }

    /**
     * Configure the factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (Attachment $attachment) {
            if (!$attachment->uploaded_by) {
                $attachment->uploader()->associate(User::factory()->create());
                $attachment->save();
            }
        });
    }
}
