<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orders = Order::all();
        $products = Product::all();

        foreach ($orders as $order) {
            $selectedProducts = $products->random(rand(1, 5));

            foreach ($selectedProducts as $product) {
                $quantity = rand(1, 10);
                $price = $product->price;
                $total = $quantity * $price;

                $order->products()->attach($product->id, [
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
