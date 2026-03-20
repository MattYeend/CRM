<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Database\Seeder;

class QuoteProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $quotes = Quote::all();
        $products = Product::all();

        foreach ($quotes as $quote) {
            $selectedProducts = $products->random(rand(1, 5));

            foreach ($selectedProducts as $product) {
                $quantity = rand(1, 10);
                $price = $product->price;
                $total = $quantity * $price;

                $quote->products()->attach($product->id, [
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
