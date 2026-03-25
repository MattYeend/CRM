<?php

namespace Database\Factories;

use App\Models\PartCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PartCategory>
 */
class PartCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);
        return [
            'parent_id' => null,
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'created_by' => User::inRandomOrder()->first()?->id ?? User::factory()->create()->id,
        ];;
    }

    public function withParent(int $parentId): static
    {
        return $this->state(fn() => ['parent_id' => $parentId]);
    }
}
