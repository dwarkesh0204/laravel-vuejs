<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'symbol' => fake()->randomElement(['BTC', 'ETH', 'SOL', 'ADA', 'DOT']),
            'side' => fake()->randomElement([Order::SIDE_BUY, Order::SIDE_SELL]),
            'price' => fake()->randomFloat(8, 100, 50000),
            'amount' => fake()->randomFloat(8, 0.001, 10),
            'filled_amount' => 0,
            'status' => Order::STATUS_OPEN,
        ];
    }

    /**
     * Indicate that the order is a buy order.
     */
    public function buy(): static
    {
        return $this->state(fn (array $attributes) => [
            'side' => Order::SIDE_BUY,
        ]);
    }

    /**
     * Indicate that the order is a sell order.
     */
    public function sell(): static
    {
        return $this->state(fn (array $attributes) => [
            'side' => Order::SIDE_SELL,
        ]);
    }

    /**
     * Indicate that the order is filled.
     */
    public function filled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Order::STATUS_FILLED,
            'filled_amount' => $attributes['amount'] ?? fake()->randomFloat(8, 0.001, 10),
        ]);
    }

    /**
     * Indicate that the order is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Order::STATUS_CANCELLED,
        ]);
    }
}

