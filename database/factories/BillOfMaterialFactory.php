<?php

namespace Database\Factories;

use App\Models\BillOfMaterial;
use App\Models\Part;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BillOfMaterial>
 */
class BillOfMaterialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'parent_part_id'  => Part::factory(),
            'child_part_id' => Part::factory(),
            'quantity' => fake()->randomFloat(2, 1, 50),
            'unit_of_measure' => fake()->randomElement(['each', 'kg', 'litre', 'metre']),
            'notes' => fake()->optional()->sentence(),
            'is_test' => true,
            'meta' => [],
            'created_by' => User::inRandomOrder()->first()?->id,
        ];
    }
}
