<?php

namespace Database\Seeders;

use App\Models\Part;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class PartSupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $parts = Part::all();
        $suppliers = Supplier::all();

        foreach ($parts as $part) {
            $randomSuppliers = $suppliers->random(rand(1, 3));

            foreach ($randomSuppliers as $supplier) {
                $part->suppliers()->attach($supplier->id, [
                    'supplier_sku' => strtoupper(fake()->bothify('SKU-#####')),
                    'unit_cost' => fake()->randomFloat(2, 1, 100),
                    'lead_time_days' => fake()->numberBetween(1, 30),
                    'is_preferred' => fake()->boolean(20),
                    'is_test' => true,
                    'meta' => json_encode(['notes' => fake()->sentence()]),
                    'created_by' => 1,
                    'updated_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
