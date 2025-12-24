<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Trade>
 */
class TradeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Trade::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $symbol = fake()->randomElement(['BTC', 'ETH', 'SOL', 'ADA', 'DOT']);

        return [
            'buy_order_id' => Order::factory()->buy()->state(['symbol' => $symbol]),
            'sell_order_id' => Order::factory()->sell()->state(['symbol' => $symbol]),
            'buyer_id' => User::factory(),
            'seller_id' => User::factory(),
            'symbol' => $symbol,
            'price' => fake()->randomFloat(8, 100, 50000),
            'amount' => fake()->randomFloat(8, 0.001, 10),
        ];
    }
}

