<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(0, 500);
        $minStock = fake()->numberBetween(5, 50);
        $reorderPt = $minStock + fake()->numberBetween(5, 20);
        $maxStock = $reorderPt + fake()->numberBetween(50, 200);

        return [
            'sku' => strtoupper(fake()->unique()->bothify('??###')),
            'name' => fake()->word(),
            'description' => fake()->optional()->sentence(),
            'price' => fake()->randomFloat(2, 1, 1000),
            'currency' => fake()->randomElement(['USD', 'GBP', 'EUR']),
            'status' => fake()->randomElement(Product::PRODUCT_STATUSES),
            'quantity' => $quantity,
            'min_stock_level' => $minStock,
            'max_stock_level' => $maxStock,
            'reorder_point' => $reorderPt,
            'reorder_quantity' => fake()->numberBetween(10, 100),
            'lead_time_days' => fake()->numberBetween(1, 90),
            'is_test' => true,
            'meta' => [],
            'created_by' => User::inRandomOrder()->first()?->id,
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => Product::ACTIVE_PRODUCT_STATUS]);
    }

    public function discontinued(): static
    {
        return $this->state(['status' => Product::DISCONTINUED_PRODUCT_STATUS]);
    }

    public function outOfStock(): static
    {
        return $this->state(['status' => Product::OUT_OF_STOCK_PRODUCT_STATUS, 'quantity' => 0]);
    }

    public function lowStock(): static
    {
        return $this->state(fn (array $attrs) => [
            'quantity' => fake()->numberBetween(1, $attrs['min_stock_level']),
            'status' => Product::ACTIVE_PRODUCT_STATUS,
        ]);
    }
}
