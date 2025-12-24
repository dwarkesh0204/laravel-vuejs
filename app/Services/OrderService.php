<?php

namespace App\Services;

use App\Events\OrderMatched;
use App\Exceptions\InsufficientAssetException;
use App\Exceptions\InsufficientBalanceException;
use App\Models\Asset;
use App\Models\Order;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class OrderService
{
    /**
     * Commission rate for trades (1.5%).
     */
    public const COMMISSION_RATE = '0.015';

    /**
     * Place a buy order with proper concurrency handling.
     *
     * This method:
     * 1. Starts a database transaction
     * 2. Locks the user record to prevent race conditions
     * 3. Validates sufficient USD balance
     * 4. Deducts the cost from user's balance immediately
     * 5. Creates the order with status 'open'
     * 6. Attempts to match the order
     *
     * @param User $user The user placing the order
     * @param string $symbol The trading symbol (e.g., 'BTC')
     * @param string $price The limit price per unit
     * @param string $amount The amount to buy
     * @return Order The created order
     * @throws InvalidArgumentException If price or amount is invalid
     * @throws InsufficientBalanceException If insufficient USD balance
     * @throws RuntimeException If user not found
     */
    public function placeBuyOrder(User $user, string $symbol, string $price, string $amount): Order
    {
        // Validate inputs
        $this->validateOrderInputs($price, $amount);

        $order = DB::transaction(function () use ($user, $symbol, $price, $amount) {
            // Lock the user record to prevent race conditions
            // This ensures no other transaction can modify the balance until we're done
            $lockedUser = User::where('id', $user->id)->lockForUpdate()->first();

            if (!$lockedUser) {
                throw new RuntimeException('User not found.');
            }

            // Calculate the total cost: price * amount
            $totalCost = bcmul($price, $amount, 8);

            // Check if user has sufficient balance
            if (bccomp($lockedUser->balance, $totalCost, 8) < 0) {
                throw new InsufficientBalanceException(
                    $totalCost,
                    $lockedUser->balance,
                    'USD'
                );
            }

            // Deduct the cost from user's balance immediately (reserve funds)
            $lockedUser->balance = bcsub($lockedUser->balance, $totalCost, 8);
            $lockedUser->save();

            // Create the buy order with status 'open'
            $order = Order::create([
                'user_id' => $lockedUser->id,
                'symbol' => strtoupper($symbol),
                'side' => Order::SIDE_BUY,
                'price' => $price,
                'amount' => $amount,
                'filled_amount' => '0',
                'status' => Order::STATUS_OPEN,
            ]);

            return $order;
        });

        // Try to match the order after creation
        $this->matchOrder($order);

        // Refresh to get updated status
        return $order->fresh();
    }

    /**
     * Place a sell order with proper concurrency handling.
     *
     * This method:
     * 1. Starts a database transaction
     * 2. Locks the asset record to prevent race conditions
     * 3. Validates sufficient asset balance
     * 4. Moves the asset amount from 'amount' to 'locked_amount'
     * 5. Creates the order with status 'open'
     * 6. Attempts to match the order
     *
     * @param User $user The user placing the order
     * @param string $symbol The trading symbol (e.g., 'BTC')
     * @param string $price The limit price per unit
     * @param string $amount The amount to sell
     * @return Order The created order
     * @throws InvalidArgumentException If price or amount is invalid
     * @throws InsufficientAssetException If insufficient asset balance
     * @throws RuntimeException If asset not found
     */
    public function placeSellOrder(User $user, string $symbol, string $price, string $amount): Order
    {
        // Validate inputs
        $this->validateOrderInputs($price, $amount);

        $symbol = strtoupper($symbol);

        $order = DB::transaction(function () use ($user, $symbol, $price, $amount) {
            // Lock the asset record to prevent race conditions
            // This ensures no other transaction can modify the asset until we're done
            $lockedAsset = Asset::where('user_id', $user->id)
                ->where('symbol', $symbol)
                ->lockForUpdate()
                ->first();

            // Check if user has this asset
            if (!$lockedAsset) {
                throw new RuntimeException(
                    sprintf('No %s asset found for this user.', $symbol)
                );
            }

            // Check if user has sufficient available asset amount
            if (bccomp($lockedAsset->amount, $amount, 8) < 0) {
                throw new InsufficientAssetException(
                    $amount,
                    $lockedAsset->amount,
                    $symbol
                );
            }

            // Move the amount from 'amount' to 'locked_amount' (reserve assets)
            $lockedAsset->amount = bcsub($lockedAsset->amount, $amount, 8);
            $lockedAsset->locked_amount = bcadd($lockedAsset->locked_amount, $amount, 8);
            $lockedAsset->save();

            // Create the sell order with status 'open'
            $order = Order::create([
                'user_id' => $user->id,
                'symbol' => $symbol,
                'side' => Order::SIDE_SELL,
                'price' => $price,
                'amount' => $amount,
                'filled_amount' => '0',
                'status' => Order::STATUS_OPEN,
            ]);

            return $order;
        });

        // Try to match the order after creation
        $this->matchOrder($order);

        // Refresh to get updated status
        return $order->fresh();
    }

    /**
     * Match an order against the order book.
     *
     * Full Match Only: Only matches if both orders have the same amount.
     *
     * Matching Logic:
     * - BUY order: Find first open SELL order where sell.price <= buy.price
     * - SELL order: Find first open BUY order where buy.price >= sell.price
     *
     * @param Order $order The order to match
     * @return Trade|null The trade if matched, null otherwise
     */
    public function matchOrder(Order $order): ?Trade
    {
        // Only match open orders
        if (!$order->isOpen()) {
            return null;
        }

        return DB::transaction(function () use ($order) {
            // Re-fetch and lock the order to ensure it's still open
            $lockedOrder = Order::where('id', $order->id)
                ->where('status', Order::STATUS_OPEN)
                ->lockForUpdate()
                ->first();

            if (!$lockedOrder) {
                return null;
            }

            // Find a matching counter-order
            $matchingOrder = $this->findMatchingOrder($lockedOrder);

            if (!$matchingOrder) {
                return null;
            }

            // Execute the trade
            return $this->executeTrade($lockedOrder, $matchingOrder);
        });
    }

    /**
     * Find a matching order for the given order.
     *
     * Full Match Only: Only returns orders with the same amount.
     *
     * @param Order $order The order to find a match for
     * @return Order|null The matching order if found
     */
    private function findMatchingOrder(Order $order): ?Order
    {
        $query = Order::where('symbol', $order->symbol)
            ->where('status', Order::STATUS_OPEN)
            ->where('user_id', '!=', $order->user_id) // Cannot trade with yourself
            ->where('amount', $order->amount) // Full Match Only: same amount
            ->lockForUpdate();

        if ($order->isBuyOrder()) {
            // For a BUY order, find SELL orders where sell.price <= buy.price
            // Order by price ASC (cheapest first), then by created_at ASC (oldest first)
            return $query->where('side', Order::SIDE_SELL)
                ->where('price', '<=', $order->price)
                ->orderBy('price', 'asc')
                ->orderBy('created_at', 'asc')
                ->first();
        } else {
            // For a SELL order, find BUY orders where buy.price >= sell.price
            // Order by price DESC (highest first), then by created_at ASC (oldest first)
            return $query->where('side', Order::SIDE_BUY)
                ->where('price', '>=', $order->price)
                ->orderBy('price', 'desc')
                ->orderBy('created_at', 'asc')
                ->first();
        }
    }

    /**
     * Execute a trade between two matching orders.
     *
     * @param Order $order The initiating order
     * @param Order $matchingOrder The matching order
     * @return Trade The executed trade
     */
    private function executeTrade(Order $order, Order $matchingOrder): Trade
    {
        // Determine buy and sell orders
        $buyOrder = $order->isBuyOrder() ? $order : $matchingOrder;
        $sellOrder = $order->isSellOrder() ? $order : $matchingOrder;

        // Trade executes at the price of the resting order (the one that was placed first)
        // In this case, since we're matching a new order against existing ones,
        // the matching order is the resting order
        $executionPrice = $matchingOrder->price;
        $tradeAmount = $order->amount; // Full match, so both amounts are equal

        // Calculate total USD volume and commission
        $totalVolume = bcmul($executionPrice, $tradeAmount, 8);
        $commission = bcmul($totalVolume, self::COMMISSION_RATE, 8);
        $sellerProceeds = bcsub($totalVolume, $commission, 8);

        // Get buyer and seller users
        $buyer = User::where('id', $buyOrder->user_id)->lockForUpdate()->first();
        $seller = User::where('id', $sellOrder->user_id)->lockForUpdate()->first();

        // Settlement for BUYER:
        // - Receives the crypto asset
        // - If buy price > execution price, refund the difference
        $buyerAsset = Asset::firstOrCreate(
            ['user_id' => $buyer->id, 'symbol' => $order->symbol],
            ['amount' => '0', 'locked_amount' => '0']
        );
        $buyerAsset = Asset::where('id', $buyerAsset->id)->lockForUpdate()->first();
        $buyerAsset->amount = bcadd($buyerAsset->amount, $tradeAmount, 8);
        $buyerAsset->save();

        // Refund buyer if they paid more than execution price
        $buyerPaid = bcmul($buyOrder->price, $tradeAmount, 8);
        $actualCost = $totalVolume;
        if (bccomp($buyerPaid, $actualCost, 8) > 0) {
            $refund = bcsub($buyerPaid, $actualCost, 8);
            $buyer->balance = bcadd($buyer->balance, $refund, 8);
            $buyer->save();
        }

        // Settlement for SELLER:
        // - Receives USD minus commission
        // - Decrease locked_amount (asset is now sold)
        $seller->balance = bcadd($seller->balance, $sellerProceeds, 8);
        $seller->save();

        $sellerAsset = Asset::where('user_id', $seller->id)
            ->where('symbol', $order->symbol)
            ->lockForUpdate()
            ->first();

        if ($sellerAsset) {
            $sellerAsset->locked_amount = bcsub($sellerAsset->locked_amount, $tradeAmount, 8);
            $sellerAsset->save();
        }

        // Update both orders to filled
        $buyOrder->filled_amount = $tradeAmount;
        $buyOrder->status = Order::STATUS_FILLED;
        $buyOrder->save();

        $sellOrder->filled_amount = $tradeAmount;
        $sellOrder->status = Order::STATUS_FILLED;
        $sellOrder->save();

        // Create trade record
        $trade = Trade::create([
            'buy_order_id' => $buyOrder->id,
            'sell_order_id' => $sellOrder->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'symbol' => $order->symbol,
            'price' => $executionPrice,
            'amount' => $tradeAmount,
        ]);

        // Dispatch OrderMatched event
        event(new OrderMatched($trade, $buyOrder, $sellOrder, $commission));

        return $trade;
    }

    /**
     * Cancel an open order and return the reserved funds/assets.
     *
     * @param Order $order The order to cancel
     * @return Order The cancelled order
     * @throws RuntimeException If order cannot be cancelled
     */
    public function cancelOrder(Order $order): Order
    {
        if (!$order->isOpen()) {
            throw new RuntimeException('Only open orders can be cancelled.');
        }

        return DB::transaction(function () use ($order) {
            // Re-fetch and lock the order to ensure it's still open
            $lockedOrder = Order::where('id', $order->id)
                ->where('status', Order::STATUS_OPEN)
                ->lockForUpdate()
                ->first();

            if (!$lockedOrder) {
                throw new RuntimeException('Order is no longer open or does not exist.');
            }

            // Calculate remaining amount (amount - filled_amount)
            $remainingAmount = bcsub($lockedOrder->amount, $lockedOrder->filled_amount, 8);

            if ($lockedOrder->isBuyOrder()) {
                // Return reserved USD to user
                $lockedUser = User::where('id', $lockedOrder->user_id)
                    ->lockForUpdate()
                    ->first();

                // Calculate the cost to return: remaining_amount * price
                $costToReturn = bcmul($remainingAmount, $lockedOrder->price, 8);
                $lockedUser->balance = bcadd($lockedUser->balance, $costToReturn, 8);
                $lockedUser->save();
            } else {
                // Return reserved assets to user
                $lockedAsset = Asset::where('user_id', $lockedOrder->user_id)
                    ->where('symbol', $lockedOrder->symbol)
                    ->lockForUpdate()
                    ->first();

                if ($lockedAsset) {
                    $lockedAsset->locked_amount = bcsub($lockedAsset->locked_amount, $remainingAmount, 8);
                    $lockedAsset->amount = bcadd($lockedAsset->amount, $remainingAmount, 8);
                    $lockedAsset->save();
                }
            }

            // Update order status to cancelled
            $lockedOrder->status = Order::STATUS_CANCELLED;
            $lockedOrder->save();

            return $lockedOrder;
        });
    }

    /**
     * Get the order book for a symbol.
     *
     * @param string $symbol The trading symbol
     * @return array{buy: \Illuminate\Support\Collection, sell: \Illuminate\Support\Collection}
     */
    public function getOrderBook(string $symbol): array
    {
        $symbol = strtoupper($symbol);

        $buyOrders = Order::where('symbol', $symbol)
            ->where('status', Order::STATUS_OPEN)
            ->where('side', Order::SIDE_BUY)
            ->orderBy('price', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();

        $sellOrders = Order::where('symbol', $symbol)
            ->where('status', Order::STATUS_OPEN)
            ->where('side', Order::SIDE_SELL)
            ->orderBy('price', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        return [
            'buy' => $buyOrders,
            'sell' => $sellOrders,
        ];
    }

    /**
     * Validate order inputs.
     *
     * @param string $price The price value
     * @param string $amount The amount value
     * @throws InvalidArgumentException If validation fails
     */
    private function validateOrderInputs(string $price, string $amount): void
    {
        // Validate price
        if (!is_numeric($price) || bccomp($price, '0', 8) <= 0) {
            throw new InvalidArgumentException('Price must be a positive number.');
        }

        // Validate amount
        if (!is_numeric($amount) || bccomp($amount, '0', 8) <= 0) {
            throw new InvalidArgumentException('Amount must be a positive number.');
        }
    }
}
