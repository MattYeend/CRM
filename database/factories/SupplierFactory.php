<?php

namespace Database\Factories;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Supplier>
 */
class SupplierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'code' => strtoupper(fake()->unique()->lexify('SUP-????')),
            'email' => fake()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'website' => fake()->url(),
            'address_line_1'  => fake()->streetAddress(),
            'city' => fake()->city(),
            'postcode' => fake()->postcode(),
            'country' => 'GB',
            'currency' => 'GBP',
            'payment_terms' => fake()->randomElement(['NET30', 'NET60', 'NET90']),
            'contact_name' => fake()->name(),
            'contact_email' => fake()->email(),
            'contact_phone' => fake()->phoneNumber(),
            'is_active' => true,
            'created_by' => User::inRandomOrder()->first()?->id ?? User::factory()->create()->id,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn() => ['is_active' => false]);
    }
}
