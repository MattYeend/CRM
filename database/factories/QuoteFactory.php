<?php

namespace Database\Factories;

use App\Models\Deal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quote>
 */
class QuoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 50, 500);
        $tax = $subtotal * 0.2;

        return [
            'deal_id' => Deal::factory(),
            'currency' => 'GBP',
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $subtotal + $tax,
            'sent_at' => null,
            'accepted_at' => null,
            'is_test' => true,
            'meta' => [],
            'created_by' => User::inRandomOrder()->value('id'),
        ];
    }
}
