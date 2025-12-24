<?php

namespace App\Events;

use App\Models\Order;
use App\Models\Trade;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderMatched implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The trade that was executed.
     */
    public Trade $trade;

    /**
     * The buy order.
     */
    public Order $buyOrder;

    /**
     * The sell order.
     */
    public Order $sellOrder;

    /**
     * The commission amount charged.
     */
    public string $commission;

    /**
     * Create a new event instance.
     */
    public function __construct(Trade $trade, Order $buyOrder, Order $sellOrder, string $commission)
    {
        $this->trade = $trade;
        $this->buyOrder = $buyOrder;
        $this->sellOrder = $sellOrder;
        $this->commission = $commission;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * Broadcasts to both the buyer and seller private channels.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->trade->buyer_id),
            new PrivateChannel('user.' . $this->trade->seller_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'order.matched';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'trade' => [
                'id' => $this->trade->id,
                'symbol' => $this->trade->symbol,
                'price' => $this->trade->price,
                'amount' => $this->trade->amount,
                'total_value' => bcmul($this->trade->price, $this->trade->amount, 8),
                'buyer_id' => $this->trade->buyer_id,
                'seller_id' => $this->trade->seller_id,
                'created_at' => $this->trade->created_at->toISOString(),
            ],
            'buy_order' => [
                'id' => $this->buyOrder->id,
                'status' => $this->buyOrder->status,
            ],
            'sell_order' => [
                'id' => $this->sellOrder->id,
                'status' => $this->sellOrder->status,
            ],
            'commission' => $this->commission,
        ];
    }
}
