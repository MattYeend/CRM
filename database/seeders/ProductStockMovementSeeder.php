<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductStockMovement;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductStockMovementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::all()->each(function (Product $product) {
            ProductStockMovement::factory()->count(5)->create(['product_id' => $product->id]);
        });
    }
}
