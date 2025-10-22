<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Company;
use App\Models\Contact;
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
            'number' => strtoupper($this->faker->unique()->bothify('INV-####')),
            'company_id' => null,
            'contact_id' => null,
            'created_by' => null,
            'issue_date' => now()->subDays(rand(0, 30)),
            'due_date' => now()->addDays(rand(7, 30)),
            'status' => 'draft',
            'subtotal' => 0,
            'tax' => 0,
            'total' => 0,
            'currency' => 'USD',
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
        return $this->afterCreating(function (Invoice $invoice) {
            if (!$invoice->company_id) {
                $invoice->company()->associate(Company::factory()->create());
            }
            if (!$invoice->contact_id) {
                $invoice->contact()->associate(Contact::factory()->create());
            }
            if (!$invoice->created_by) {
                $invoice->creator()->associate(User::factory()->create());
            }
            $invoice->save();
        });
    }
}
