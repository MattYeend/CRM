<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = fake()->randomFloat(2, 50, 1000);

        return [
            'assigned_to' => User::inRandomOrder()->first()?->id,
            'deal_id' => null, 
            'amount' => fake()->randomFloat(2, 50, 1000),
            'currency' => fake()->randomElement(['GBP', 'USD', 'EUR']),
            'status' => fake()->randomElement(['pending', 'paid', 'failed']),
            'payment_method' => fake()->optional()->randomElement(['card', 'paypal', 'stripe']),
            'paid_at' => fake()->optional()->dateTimeBetween('-1 month', 'now')?->format('Y-m-d H:i:s'),
            'payment_intent_id' => fake()->uuid(),
            'charge_id' => fake()->uuid(),
            'stripe_payment_intent' => fake()->uuid(),
            'stripe_invoice_id' => fake()->uuid(),
            'is_test' => true,
            'meta' => [],
            'created_by' => User::inRandomOrder()->first()?->id,
        ];
    }
}
