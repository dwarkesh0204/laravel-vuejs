<?php

namespace App\Listeners;

use App\Events\OrderMatched;
use Illuminate\Support\Facades\Log;

class LogOrderMatched
{
    /**
     * Handle the event.
     */
    public function handle(OrderMatched $event): void
    {
        Log::info('Order matched', [
            'trade_id' => $event->trade->id,
            'symbol' => $event->trade->symbol,
            'price' => $event->trade->price,
            'amount' => $event->trade->amount,
            'buyer_id' => $event->trade->buyer_id,
            'seller_id' => $event->trade->seller_id,
            'buy_order_id' => $event->buyOrder->id,
            'sell_order_id' => $event->sellOrder->id,
            'commission' => $event->commission,
            'total_volume' => bcmul($event->trade->price, $event->trade->amount, 8),
        ]);
    }
}

