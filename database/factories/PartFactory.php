<?php

namespace Database\Factories;

use App\Models\Part;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

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
        $type = $this->faker->randomElement([
            'raw_material', 'finished_good', 'consumable', 'spare_part', 'sub_assembly',
        ]);

        $price = $this->faker->randomFloat(2, 1, 500);
        $costPrice = round($price * $this->faker->randomFloat(2, 0.4, 0.8), 2);

        $quantity = $this->faker->numberBetween(0, 500);
        $minStock = $this->faker->numberBetween(5, 50);
        $reorderPt = $minStock + $this->faker->numberBetween(5, 20);
        $maxStock = $reorderPt + $this->faker->numberBetween(50, 200);

        return [
            'product_id'           => Product::factory(),
            // 'category_id'          => $this->faker->boolean(70) ? PartCategory::factory() : null,
            // 'supplier_id'          => $this->faker->boolean(80) ? Supplier::factory() : null,

            'sku'                  => strtoupper($this->faker->unique()->bothify('SKU-####-???')),
            'part_number'          => $this->faker->unique()->optional(0.8)->bothify('PN-???-#####'),
            'barcode'              => $this->faker->unique()->optional(0.7)->ean13(),
            'name'                 => $this->faker->words(3, true),
            'description'          => $this->faker->sentence(),
            'brand'                => $this->faker->optional(0.7)->company(),
            'manufacturer'         => $this->faker->optional(0.6)->company(),
            'type'                 => $type,
            'status'               => $this->faker->randomElement(['active', 'active', 'active', 'discontinued', 'pending', 'out_of_stock']),
            'unit_of_measure'      => $this->faker->randomElement(['each', 'kg', 'litre', 'metre', 'box', 'pair']),

            // Physical Attributes
            'height'               => $this->faker->optional(0.7)->randomFloat(2, 1, 100),
            'width'                => $this->faker->optional(0.7)->randomFloat(2, 1, 100),
            'length'               => $this->faker->optional(0.7)->randomFloat(2, 1, 200),
            'weight'               => $this->faker->optional(0.8)->randomFloat(2, 0.01, 50),
            'volume'               => $this->faker->optional(0.5)->randomFloat(2, 0.01, 100),
            'colour'               => $this->faker->optional(0.6)->safeColorName(),
            'material'             => $this->faker->optional(0.5)->randomElement(['Steel', 'Aluminium', 'Plastic', 'Rubber', 'Copper', 'Brass']),

            // Pricing & Tax
            'price'                => $price,
            'cost_price'           => $costPrice,
            'currency'             => 'GBP',
            'tax_rate'             => $this->faker->randomElement([0.00, 5.00, 20.00]),
            'tax_code'             => $this->faker->randomElement(['T0', 'T1', 'T2']),
            'discount_percentage'  => $this->faker->optional(0.3)->randomFloat(2, 0, 30),

            // Inventory & Warehouse
            'quantity'             => $quantity,
            'min_stock_level'      => $minStock,
            'max_stock_level'      => $maxStock,
            'reorder_point'        => $reorderPt,
            'reorder_quantity'     => $this->faker->numberBetween(10, 100),
            'lead_time_days'       => $this->faker->numberBetween(1, 90),
            'warehouse_location'   => $this->faker->optional(0.8)->randomElement(['Warehouse A', 'Warehouse B', 'Warehouse C']),
            'bin_location'         => $this->faker->optional(0.8)->bothify('Shelf ??'),

            // Feature Flags
            'is_active'            => $this->faker->boolean(85),
            'is_purchasable'       => $this->faker->boolean(90),
            'is_sellable'          => $this->faker->boolean(90),
            'is_manufactured'      => $type === 'sub_assembly' || $this->faker->boolean(20),
            'is_serialised'        => $this->faker->boolean(15),
            'is_batch_tracked'     => $this->faker->boolean(20),
            'is_test'              => false,

            // Meta
            'meta'                 => null,

            // Audit
            'created_by'           => User::factory(),
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
            'quantity'    => $this->faker->numberBetween(1, $attrs['min_stock_level']),
            'status'      => 'active',
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
