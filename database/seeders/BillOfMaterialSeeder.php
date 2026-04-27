<?php

namespace Database\Seeders;

use App\Models\Part;
use App\Models\Product;
use App\Models\BillOfMaterial;
use App\Models\User;
use Illuminate\Database\Seeder;

class BillOfMaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedPartBoms();
        $this->seedProductBoms();
    }

    /**
     * Seed BOMs for manufactured parts.
     */
    protected function seedPartBoms(): void
    {
        $parts = Part::where('is_manufactured', true)->get();
        $childParts = Part::where('is_manufactured', false)->pluck('id');

        if ($childParts->isEmpty()) {
            return;
        }

        $parts->each(function (Part $part) use ($childParts) {
            $childParts->random(min(3, $childParts->count()))
                ->each(function ($childId) use ($part) {
                    BillOfMaterial::firstOrCreate(
                        [
                            'manufacturable_type' => Part::class,
                            'manufacturable_id' => $part->id,
                            'child_part_id' => $childId,
                        ],
                        [
                            'quantity' => fake()->randomFloat(4, 0.1, 10),
                            'scrap_percentage' => fake()->randomFloat(2, 0, 5),
                            'unit_of_measure' => 'each',
                            'is_test' => true,
                            'created_by' => User::inRandomOrder()->first()?->id,
                        ]
                    );
                });
        });
    }

    /**
     * Seed BOMs for products that are assembled from parts.
     */
    protected function seedProductBoms(): void
    {
        // Get products that could be manufactured from parts
        $products = Product::whereNotNull('sku')
            ->take(5) // Limit to a reasonable number
            ->get();

        $availableParts = Part::pluck('id');

        if ($availableParts->isEmpty()) {
            return;
        }

        $products->each(function (Product $product) use ($availableParts) {
            // Create BOMs for 2-4 parts per product
            $availableParts->random(min(rand(2, 4), $availableParts->count()))
                ->each(function ($partId) use ($product) {
                    BillOfMaterial::firstOrCreate(
                        [
                            'manufacturable_type' => Product::class,
                            'manufacturable_id' => $product->id,
                            'child_part_id' => $partId,
                        ],
                        [
                            'quantity' => fake()->randomFloat(4, 1, 5),
                            'scrap_percentage' => fake()->randomFloat(2, 0, 3),
                            'unit_of_measure' => 'each',
                            'is_test' => true,
                            'created_by' => User::inRandomOrder()->first()?->id,
                        ]
                    );
                });
        });
    }
}
