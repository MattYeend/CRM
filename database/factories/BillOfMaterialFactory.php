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
     * CRITICAL: Uses morph map aliases ('part', 'product') to match
     * how the application queries records via getMorphClass().
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $aliases = ['part', 'product'];
        $alias = fake()->randomElement($aliases);
        
        $modelClass = match($alias) {
            'part' => Part::class,
            'product' => Product::class,
        };

        $manufacturable = $modelClass::factory()->create(
            $alias === 'part' ? ['is_manufactured' => true] : []
        );

        return [
            'manufacturable_type' => $alias,
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
     * @param  Part|null $part
     * @return static
     */
    public function forPart(?Part $part = null): static
    {
        $part = $part ?? Part::factory()->create(['is_manufactured' => true]);

        return $this->state(fn (array $attributes) => [
            'manufacturable_type' => 'part',
            'manufacturable_id' => $part->id,
            'child_part_id' => $attributes['child_part_id'] ?? Part::factory(),
        ]);
    }

    /**
     * Set the manufacturable to a Product.
     *
     * @param  Product|null $product
     * @return static
     */
    public function forProduct(?Product $product = null): static
    {
        $product = $product ?? Product::factory()->create();

        return $this->state(fn (array $attributes) => [
            'manufacturable_type' => 'product',
            'manufacturable_id' => $product->id,
            'child_part_id' => $attributes['child_part_id'] ?? Part::factory(),
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
        return $this->state(fn () => [
            'child_part_id' => $part->id,
        ]);
    }
}
