<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ExchangeSeeder extends Seeder
{
    /**
     * Seed the exchange with test users, assets, and orders.
     *
     * Creates:
     * - 2 test users with USD balance
     * - Crypto assets for each user
     * - Sample open orders for demonstration
     */
    public function run(): void
    {
        // Create User 1 - Alice (Buyer with USD balance)
        $alice = User::create([
            'name' => 'Alice Trader',
            'email' => 'alice@example.com',
            'password' => Hash::make('password'),
            'balance' => '100000.00000000', // $100,000 USD
        ]);

        // Create User 2 - Bob (Seller with crypto assets)
        $bob = User::create([
            'name' => 'Bob Trader',
            'email' => 'bob@example.com',
            'password' => Hash::make('password'),
            'balance' => '25000.00000000', // $25,000 USD
        ]);

        // Create User 3 - Charlie (Mixed trader)
        $charlie = User::create([
            'name' => 'Charlie Trader',
            'email' => 'charlie@example.com',
            'password' => Hash::make('password'),
            'balance' => '100000.00000000', // $100,000 USD
        ]);

        // Give Alice some crypto assets
        Asset::create([
            'user_id' => $alice->id,
            'symbol' => 'BTC',
            'amount' => '2.50000000', // 2.5 BTC
            'locked_amount' => '0.00000000',
        ]);

        Asset::create([
            'user_id' => $alice->id,
            'symbol' => 'ETH',
            'amount' => '15.00000000', // 15 ETH
            'locked_amount' => '0.00000000',
        ]);

        // Give Bob crypto assets (he'll be a seller)
        Asset::create([
            'user_id' => $bob->id,
            'symbol' => 'BTC',
            'amount' => '5.00000000', // 5 BTC
            'locked_amount' => '0.00000000',
        ]);

        Asset::create([
            'user_id' => $bob->id,
            'symbol' => 'ETH',
            'amount' => '50.00000000', // 50 ETH
            'locked_amount' => '0.00000000',
        ]);

        Asset::create([
            'user_id' => $bob->id,
            'symbol' => 'SOL',
            'amount' => '100.00000000', // 100 SOL
            'locked_amount' => '0.00000000',
        ]);

        // Give Charlie crypto assets
        Asset::create([
            'user_id' => $charlie->id,
            'symbol' => 'BTC',
            'amount' => '10.00000000', // 10 BTC
            'locked_amount' => '0.00000000',
        ]);

        Asset::create([
            'user_id' => $charlie->id,
            'symbol' => 'ETH',
            'amount' => '100.00000000', // 100 ETH
            'locked_amount' => '0.00000000',
        ]);

        Asset::create([
            'user_id' => $charlie->id,
            'symbol' => 'SOL',
            'amount' => '500.00000000', // 500 SOL
            'locked_amount' => '0.00000000',
        ]);

        Asset::create([
            'user_id' => $charlie->id,
            'symbol' => 'ADA',
            'amount' => '10000.00000000', // 10,000 ADA
            'locked_amount' => '0.00000000',
        ]);

        // Create some sample OPEN orders for the order book
        // These will be visible in the order book for demonstration

        // Bob's sell orders (BTC)
        Order::create([
            'user_id' => $bob->id,
            'symbol' => 'BTC',
            'side' => Order::SIDE_SELL,
            'price' => '45000.00000000',
            'amount' => '0.50000000',
            'filled_amount' => '0.00000000',
            'status' => Order::STATUS_OPEN,
        ]);
        // Lock Bob's BTC for the sell order
        $bobBtc = Asset::where('user_id', $bob->id)->where('symbol', 'BTC')->first();
        $bobBtc->amount = bcsub($bobBtc->amount, '0.50000000', 8);
        $bobBtc->locked_amount = bcadd($bobBtc->locked_amount, '0.50000000', 8);
        $bobBtc->save();

        Order::create([
            'user_id' => $bob->id,
            'symbol' => 'BTC',
            'side' => Order::SIDE_SELL,
            'price' => '46000.00000000',
            'amount' => '1.00000000',
            'filled_amount' => '0.00000000',
            'status' => Order::STATUS_OPEN,
        ]);
        $bobBtc->amount = bcsub($bobBtc->amount, '1.00000000', 8);
        $bobBtc->locked_amount = bcadd($bobBtc->locked_amount, '1.00000000', 8);
        $bobBtc->save();

        // Charlie's sell orders (BTC)
        Order::create([
            'user_id' => $charlie->id,
            'symbol' => 'BTC',
            'side' => Order::SIDE_SELL,
            'price' => '44500.00000000',
            'amount' => '0.50000000',
            'filled_amount' => '0.00000000',
            'status' => Order::STATUS_OPEN,
        ]);
        $charlieBtc = Asset::where('user_id', $charlie->id)->where('symbol', 'BTC')->first();
        $charlieBtc->amount = bcsub($charlieBtc->amount, '0.50000000', 8);
        $charlieBtc->locked_amount = bcadd($charlieBtc->locked_amount, '0.50000000', 8);
        $charlieBtc->save();

        // Alice's buy orders (BTC) - Deduct USD from balance
        Order::create([
            'user_id' => $alice->id,
            'symbol' => 'BTC',
            'side' => Order::SIDE_BUY,
            'price' => '43000.00000000',
            'amount' => '0.50000000',
            'filled_amount' => '0.00000000',
            'status' => Order::STATUS_OPEN,
        ]);
        $alice->balance = bcsub($alice->balance, bcmul('43000.00000000', '0.50000000', 8), 8);
        $alice->save();

        Order::create([
            'user_id' => $alice->id,
            'symbol' => 'BTC',
            'side' => Order::SIDE_BUY,
            'price' => '42000.00000000',
            'amount' => '1.00000000',
            'filled_amount' => '0.00000000',
            'status' => Order::STATUS_OPEN,
        ]);
        $alice->balance = bcsub($alice->balance, bcmul('42000.00000000', '1.00000000', 8), 8);
        $alice->save();

        // ETH Orders
        Order::create([
            'user_id' => $bob->id,
            'symbol' => 'ETH',
            'side' => Order::SIDE_SELL,
            'price' => '2500.00000000',
            'amount' => '5.00000000',
            'filled_amount' => '0.00000000',
            'status' => Order::STATUS_OPEN,
        ]);
        $bobEth = Asset::where('user_id', $bob->id)->where('symbol', 'ETH')->first();
        $bobEth->amount = bcsub($bobEth->amount, '5.00000000', 8);
        $bobEth->locked_amount = bcadd($bobEth->locked_amount, '5.00000000', 8);
        $bobEth->save();

        Order::create([
            'user_id' => $charlie->id,
            'symbol' => 'ETH',
            'side' => Order::SIDE_BUY,
            'price' => '2300.00000000',
            'amount' => '5.00000000',
            'filled_amount' => '0.00000000',
            'status' => Order::STATUS_OPEN,
        ]);
        $charlie->balance = bcsub($charlie->balance, bcmul('2300.00000000', '5.00000000', 8), 8);
        $charlie->save();

        // SOL Orders
        Order::create([
            'user_id' => $bob->id,
            'symbol' => 'SOL',
            'side' => Order::SIDE_SELL,
            'price' => '150.00000000',
            'amount' => '10.00000000',
            'filled_amount' => '0.00000000',
            'status' => Order::STATUS_OPEN,
        ]);
        $bobSol = Asset::where('user_id', $bob->id)->where('symbol', 'SOL')->first();
        $bobSol->amount = bcsub($bobSol->amount, '10.00000000', 8);
        $bobSol->locked_amount = bcadd($bobSol->locked_amount, '10.00000000', 8);
        $bobSol->save();

        $this->command->info('Exchange seeder completed!');
        $this->command->info('');
        $this->command->info('Test Users Created:');
        $this->command->table(
            ['Name', 'Email', 'Password', 'USD Balance'],
            [
                ['Alice Trader', 'alice@example.com', 'password', '$' . number_format($alice->balance, 2)],
                ['Bob Trader', 'bob@example.com', 'password', '$' . number_format($bob->balance, 2)],
                ['Charlie Trader', 'charlie@example.com', 'password', '$' . number_format($charlie->balance, 2)],
            ]
        );
        $this->command->info('');
        $this->command->info('Sample orders have been created in the order book.');
        $this->command->info('Login with any user to start trading!');
    }
}

