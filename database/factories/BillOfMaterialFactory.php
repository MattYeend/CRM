<?php

namespace Database\Factories;

use App\Models\BillOfMaterial;
use App\Models\Part;
use App\Models\Product;
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
        // Default to Part as the manufacturable
        $manufacturable = Part::factory()->create(['is_manufactured' => true]);

        return [
            'manufacturable_type' => get_class($manufacturable),
            'manufacturable_id' => $manufacturable->id,
            'child_part_id' => Part::factory(),
            'quantity' => fake()->randomFloat(4, 0.1, 50),
            'scrap_percentage' => fake()->randomFloat(2, 0, 10),
            'unit_of_measure' => fake()->randomElement(['each', 'kg', 'litre', 'metre']),
            'notes' => fake()->optional()->sentence(),
            'is_test' => true,
            'meta' => [],
            'created_by' => User::inRandomOrder()->first()?->id,
        ];
    }

    /**
     * Set the manufacturable to a Part.
     *
     * @param  Part|null  $part
     * @return static
     */
    public function forPart(?Part $part = null): static
    {
        $part = $part ?? Part::factory()->create(['is_manufactured' => true]);

        return $this->state(fn (array $attributes) => [
            'manufacturable_type' => Part::class,
            'manufacturable_id' => $part->id,
        ]);
    }

    /**
     * Set the manufacturable to a Product.
     *
     * @param  Product|null  $product
     * @return static
     */
    public function forProduct(?Product $product = null): static
    {
        $product = $product ?? Product::factory()->create();

        return $this->state(fn (array $attributes) => [
            'manufacturable_type' => Product::class,
            'manufacturable_id' => $product->id,
        ]);
    }

    /**
     * Set a specific child part.
     *
     * @param  Part  $part
     * @return static
     */
    public function withChildPart(Part $part): static
    {
        return $this->state(fn (array $attributes) => [
            'child_part_id' => $part->id,
        ]);
    }
}
