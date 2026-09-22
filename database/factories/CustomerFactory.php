<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'customer' => fake()->name(),
            'customer_type' => fake()->randomElement(Customer::TYPES),
            'email' => fake()->unique()->safeEmail(),
        ];
    }
}
