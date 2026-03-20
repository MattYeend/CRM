<?php

namespace Database\Seeders;

use App\Models\Deal;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DealProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $deals = Deal::all();
        $products = Product::all();

        foreach ($deals as $deal) {
            $selectedProducts = $products->random(rand(1, 5));

            foreach ($selectedProducts as $product) {
                $quantity = rand(1, 10);
                $price = $product->price;
                $total = $quantity * $price;

                $deal->products()->attach($product->id, [
                    'quantity' => $quantity,
                    'price' => $price,
                    'total' => $total,
                    'is_test' => true,
                    'created_by' => User::inRandomOrder()->first()?->id,
                    'created_at' => now(),
                ]);
            }
        }
    }
}
