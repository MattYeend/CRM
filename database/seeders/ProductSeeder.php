<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::factory()
            ->count(40)
            ->active()
            ->create();

        Product::factory()
            ->count(10)
            ->lowStock()
            ->create();

        Product::factory()
            ->count(10)
            ->outOfStock()
            ->create();

        Product::factory()
            ->count(10)
            ->discontinued()
            ->create();
    }
}
