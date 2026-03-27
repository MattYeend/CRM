<?php

namespace Database\Factories;

use App\Models\Part;
use App\Models\PartStockMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PartStockMovement>
 */
class PartStockMovementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $before = fake()->numberBetween(0, 500);
        $quantity = fake()->numberBetween(1, 100);
        $type = fake()->randomElement(['in', 'out', 'adjustment', 'transfer', 'return']);
        $after = $type === 'out' ? max(0, $before - $quantity) : $before + $quantity;

        return [
            'part_id' => Part::factory(),
            'type' => $type,
            'quantity' => $type === 'out' ? -$quantity : $quantity,
            'quantity_before' => $before,
            'quantity_after' => $after,
            'reference' => strtoupper(fake()->bothify('REF-#####')),
            'notes' => fake()->optional()->sentence(),
            'created_by' => User::inRandomOrder()->first()?->id,
        ];
    }
}
