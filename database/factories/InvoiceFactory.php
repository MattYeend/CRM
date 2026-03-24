<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => strtoupper(fake()->unique()->bothify('INV-####')),
            'company_id' => Company::inRandomOrder()->first()?->id,
            'issue_date' => now()->subDays(rand(0, 30)),
            'due_date' => now()->addDays(rand(7, 30)),
            'status' => 'draft',
            'subtotal' => 0,
            'tax' => 0,
            'total' => 0,
            'currency' => fake()->randomElement(['GBP', 'USD', 'EUR']),
            'is_test' => true,
            'meta' => [],
            'created_by' => User::inRandomOrder()->first()?->id,
        ];
    }
}
