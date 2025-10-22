<?php

namespace Database\Factories;

use App\Models\InvoiceItem;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_id' => null,
            'product_id' => null,
            'description' => $this->faker->sentence(3),
            'quantity' => $this->faker->numberBetween(1, 10),
            'unit_price' => $this->faker->randomFloat(2, 1, 500),
            'line_total' => 0,
            'meta' => [],
        ];
    }

    /**
     * Configure the factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (InvoiceItem $item) {
            if (!$item->invoice_id) {
                $invoice = Invoice::factory()->create();
                $item->invoice()->associate($invoice);
            }
            if (!$item->product_id) {
                $product = Product::factory()->create();
                $item->product()->associate($product);
            }
            // compute line_total
            $item->line_total = bcmul((string)$item->quantity, (string)$item->unit_price, 2);
            $item->save();
        });
    }
}
