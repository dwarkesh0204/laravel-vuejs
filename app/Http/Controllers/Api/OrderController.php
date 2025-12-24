<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InsufficientAssetException;
use App\Exceptions\InsufficientBalanceException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use RuntimeException;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    /**
     * Get user profile with balance and assets.
     *
     * GET /api/profile
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'balance' => $user->balance,
                ],
                'assets' => $user->assets->map(function ($asset) {
                    return [
                        'symbol' => $asset->symbol,
                        'amount' => $asset->amount,
                        'locked_amount' => $asset->locked_amount,
                        'total_amount' => $asset->total_amount,
                    ];
                }),
                'open_orders' => $user->orders()
                    ->where('status', Order::STATUS_OPEN)
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function ($order) {
                        return [
                            'id' => $order->id,
                            'symbol' => $order->symbol,
                            'side' => $order->side,
                            'price' => $order->price,
                            'amount' => $order->amount,
                            'filled_amount' => $order->filled_amount,
                            'remaining_amount' => $order->remaining_amount,
                            'status' => $order->status,
                            'created_at' => $order->created_at->toISOString(),
                        ];
                    }),
            ],
        ]);
    }

    /**
     * Place a new order.
     *
     * POST /api/orders
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'symbol' => ['required', 'string', 'max:10'],
            'side' => ['required', Rule::in([Order::SIDE_BUY, Order::SIDE_SELL])],
            'price' => ['required', 'numeric', 'gt:0'],
            'amount' => ['required', 'numeric', 'gt:0'],
        ]);

        try {
            $user = $request->user();

            if ($validated['side'] === Order::SIDE_BUY) {
                $order = $this->orderService->placeBuyOrder(
                    $user,
                    $validated['symbol'],
                    $validated['price'],
                    $validated['amount']
                );
            } else {
                $order = $this->orderService->placeSellOrder(
                    $user,
                    $validated['symbol'],
                    $validated['price'],
                    $validated['amount']
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully.',
                'data' => [
                    'order' => [
                        'id' => $order->id,
                        'symbol' => $order->symbol,
                        'side' => $order->side,
                        'price' => $order->price,
                        'amount' => $order->amount,
                        'filled_amount' => $order->filled_amount,
                        'remaining_amount' => $order->remaining_amount,
                        'status' => $order->status,
                        'status_label' => $this->getStatusLabel($order->status),
                        'created_at' => $order->created_at->toISOString(),
                    ],
                ],
            ], 201);
        } catch (InsufficientBalanceException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => [
                    'type' => 'insufficient_balance',
                    'required' => $e->getRequired(),
                    'available' => $e->getAvailable(),
                    'currency' => $e->getCurrency(),
                ],
            ], 422);
        } catch (InsufficientAssetException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => [
                    'type' => 'insufficient_asset',
                    'required' => $e->getRequired(),
                    'available' => $e->getAvailable(),
                    'symbol' => $e->getSymbol(),
                ],
            ], 422);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => [
                    'type' => 'validation_error',
                ],
            ], 422);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => [
                    'type' => 'runtime_error',
                ],
            ], 400);
        }
    }

    /**
     * Cancel an order.
     *
     * POST /api/orders/{id}/cancel
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $order = Order::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
                'error' => [
                    'type' => 'not_found',
                ],
            ], 404);
        }

        if (!$order->isOpen()) {
            return response()->json([
                'success' => false,
                'message' => 'Only open orders can be cancelled.',
                'error' => [
                    'type' => 'invalid_status',
                    'current_status' => $order->status,
                    'status_label' => $this->getStatusLabel($order->status),
                ],
            ], 422);
        }

        try {
            $cancelledOrder = $this->orderService->cancelOrder($order);

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully.',
                'data' => [
                    'order' => [
                        'id' => $cancelledOrder->id,
                        'symbol' => $cancelledOrder->symbol,
                        'side' => $cancelledOrder->side,
                        'price' => $cancelledOrder->price,
                        'amount' => $cancelledOrder->amount,
                        'filled_amount' => $cancelledOrder->filled_amount,
                        'status' => $cancelledOrder->status,
                        'status_label' => $this->getStatusLabel($cancelledOrder->status),
                    ],
                ],
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => [
                    'type' => 'runtime_error',
                ],
            ], 400);
        }
    }

    /**
     * Get the order book for a symbol.
     *
     * GET /api/orderbook/{symbol}
     */
    public function orderBook(string $symbol): JsonResponse
    {
        $orderBook = $this->orderService->getOrderBook($symbol);

        return response()->json([
            'success' => true,
            'data' => [
                'symbol' => strtoupper($symbol),
                'buy_orders' => $orderBook['buy']->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'price' => $order->price,
                        'amount' => $order->amount,
                        'created_at' => $order->created_at->toISOString(),
                    ];
                }),
                'sell_orders' => $orderBook['sell']->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'price' => $order->price,
                        'amount' => $order->amount,
                        'created_at' => $order->created_at->toISOString(),
                    ];
                }),
            ],
        ]);
    }

    /**
     * Get user's order history.
     *
     * GET /api/orders
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $orders = $user->orders()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => [
                'orders' => $orders->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'symbol' => $order->symbol,
                        'side' => $order->side,
                        'price' => $order->price,
                        'amount' => $order->amount,
                        'filled_amount' => $order->filled_amount,
                        'remaining_amount' => $order->remaining_amount,
                        'status' => $order->status,
                        'status_label' => $this->getStatusLabel($order->status),
                        'created_at' => $order->created_at->toISOString(),
                    ];
                }),
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                ],
            ],
        ]);
    }

    /**
     * Get status label for an order status.
     */
    private function getStatusLabel(int $status): string
    {
        return match ($status) {
            Order::STATUS_OPEN => 'open',
            Order::STATUS_FILLED => 'filled',
            Order::STATUS_CANCELLED => 'cancelled',
            default => 'unknown',
        };
    }
}


