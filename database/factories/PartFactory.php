<?php

namespace Database\Factories;

use App\Models\Part;
use App\Models\PartCategory;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Part>
 */
class PartFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement([
            'raw_material', 'finished_good', 'consumable', 'spare_part', 'sub_assembly',
        ]);

        $price = fake()->randomFloat(2, 1, 500);
        $costPrice = round($price * fake()->randomFloat(2, 0.4, 0.8), 2);

        $quantity = fake()->numberBetween(0, 500);
        $minStock = fake()->numberBetween(5, 50);
        $reorderPt = $minStock + fake()->numberBetween(5, 20);
        $maxStock = $reorderPt + fake()->numberBetween(50, 200);

        return [
            'product_id' => Product::inRandomOrder()->first()?->id ?? Product::factory()->create()->id,
            'category_id' => fake()->boolean(70) ? PartCategory::inRandomOrder()->first()?->id : null,
            'supplier_id' => fake()->boolean(80) ? Supplier::inRandomOrder()->first()?->id : null,

            'sku' => 'SKU-' . strtoupper(Str::random(4)) . '-' . fake()->numberBetween(1000, 9999),
            'part_number' => fake()->optional(0.8) 
                ? 'PN-' . strtoupper(Str::random(3)) . '-' . fake()->numberBetween(10000, 99999)
                : null,

            'barcode' => fake()->optional(0.7)
                ? fake()->ean13()
                : null,
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'brand' => fake()->optional(0.7)->company(),
            'manufacturer' => fake()->optional(0.6)->company(),
            'type' => $type,
            'status' => fake()->randomElement(['active', 'discontinued', 'pending', 'out_of_stock']),
            'unit_of_measure' => fake()->randomElement(['each', 'kg', 'litre', 'metre', 'box', 'pair']),

            // Physical Attributes
            'height' => fake()->optional(0.7)->randomFloat(2, 1, 100),
            'width' => fake()->optional(0.7)->randomFloat(2, 1, 100),
            'length' => fake()->optional(0.7)->randomFloat(2, 1, 200),
            'weight' => fake()->optional(0.8)->randomFloat(2, 0.01, 50),
            'volume' => fake()->optional(0.5)->randomFloat(2, 0.01, 100),
            'colour' => fake()->optional(0.6)->safeColorName(),
            'material' => fake()->optional(0.5)->randomElement(['Steel', 'Aluminium', 'Plastic', 'Rubber', 'Copper', 'Brass']),

            // Pricing & Tax
            'price' => $price,
            'cost_price' => $costPrice,
            'currency' => 'GBP',
            'tax_rate' => fake()->randomElement([0.00, 5.00, 20.00]),
            'tax_code' => fake()->randomElement(['T0', 'T1', 'T2']),
            'discount_percentage' => fake()->optional(0.3)->randomFloat(2, 0, 30),

            // Inventory & Warehouse
            'quantity' => $quantity,
            'min_stock_level' => $minStock,
            'max_stock_level' => $maxStock,
            'reorder_point' => $reorderPt,
            'reorder_quantity' => fake()->numberBetween(10, 100),
            'lead_time_days' => fake()->numberBetween(1, 90),
            'warehouse_location' => fake()->optional(0.8)->randomElement(['Warehouse A', 'Warehouse B', 'Warehouse C']),
            'bin_location' => fake()->optional(0.8)->bothify('Shelf ??'),

            // Feature Flags
            'is_active' => fake()->boolean(85),
            'is_purchasable' => fake()->boolean(90),
            'is_sellable' => fake()->boolean(90),
            'is_manufactured' => $type === 'sub_assembly' || $this->faker->boolean(20),
            'is_serialised' => fake()->boolean(15),
            'is_batch_tracked' => fake()->boolean(20),
            'is_test' => true,

            // Meta
            'meta' => null,

            // Audit
            'created_by' => User::inRandomOrder()->first()?->id ?? User::factory()->create()->id,
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => 'active', 'is_active' => true]);
    }

    public function discontinued(): static
    {
        return $this->state(['status' => 'discontinued', 'is_active' => false]);
    }

    public function outOfStock(): static
    {
        return $this->state(['status' => 'out_of_stock', 'quantity' => 0]);
    }

    public function lowStock(): static
    {
        return $this->state(fn (array $attrs) => [
            'quantity' => fake()->numberBetween(1, $attrs['min_stock_level']),
            'status' => 'active',
        ]);
    }

    public function serialised(): static
    {
        return $this->state(['is_serialised' => true, 'is_batch_tracked' => false]);
    }

    public function batchTracked(): static
    {
        return $this->state(['is_batch_tracked' => true, 'is_serialised' => false]);
    }

    public function manufactured(): static
    {
        return $this->state(['is_manufactured' => true, 'type' => 'sub_assembly']);
    }

    public function testRecord(): static
    {
        return $this->state(['is_test' => true]);
    }
}
