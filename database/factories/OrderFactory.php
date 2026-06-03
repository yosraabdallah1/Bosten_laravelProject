<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'total'   => fake()->randomFloat(2, 20, 500),
            'status'  => fake()->randomElement(['pending', 'confirmed', 'shipped', 'delivered', 'cancelled']),
            'address' => fake()->address(),
            'phone'   => fake()->phoneNumber(),
        ];
    }

    public function pending(): static  { return $this->state(['status' => 'pending']); }
    public function confirmed(): static { return $this->state(['status' => 'confirmed']); }
    public function delivered(): static { return $this->state(['status' => 'delivered']); }
    public function cancelled(): static { return $this->state(['status' => 'cancelled']); }
}
