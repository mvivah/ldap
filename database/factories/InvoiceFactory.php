<?php

namespace Database\Factories;

use App\Models\Customer;
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
        $status = $this->faker->randomElement(['billed', 'paid', 'unpaid', 'overdue', 'void']);
        return [
            'customer_id' => Customer::factory(),
            'invoice_date' => $this->faker->date(),
            'due_date' => $this->faker->date(),
            'amount' => $this->faker->randomFloat(2, 2000, 50000),
            'status' => $status,
            'billed_at' => $this->faker->optional()->dateTimeThisDecade(),
            'paid_at' => $status === 'paid' ? $this->faker->dateTimeThisDecade() : null,
        ];
    }
}
