<?php

namespace Database\Factories;

use App\Models\Part;
use App\Models\PartSupplier;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PartSupplier>
 */
class PartSupplierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'part_id' => Part::inRandomOrder()->first()?->id,
            'supplier_id' => Supplier::inRandomOrder()->first()?->id,

            'supplier_sku' => strtoupper(fake()->bothify('SKU-#####')),
            'unit_cost' => fake()->randomFloat(2, 1, 100),
            'lead_time_days' => fake()->numberBetween(1, 30),

            'is_preferred' => fake()->boolean(20),
            'is_test' => true,

            'meta' => [
                'notes' => fake()->sentence(),
            ],

            'created_by' => User::inRandomOrder()->first()?->id ?? User::factory()->create()->id,
            'created_at' => now(),
        ];
    }
}
