<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;

class OrderMatched implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public function __construct(
        public Order $buyOrder,
        public Order $sellOrder,
        public string $price,
        public string $amount,
        public string $fee
    ) {}

    public function broadcastOn()
    {
        Log::info('Broadcasting OrderMatched', [
            'buy_user' => $this->buyOrder->user_id,
            'sell_user' => $this->sellOrder->user_id,
        ]);


        return [
            new PrivateChannel('user.' . $this->buyOrder->user_id),
            new PrivateChannel('user.' . $this->sellOrder->user_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'buy_order_id'  => $this->buyOrder->id,
            'sell_order_id' => $this->sellOrder->id,
            'symbol'        => $this->buyOrder->symbol,
            'price'         => $this->price,
            'amount'        => $this->amount,
            'fee'           => $this->fee,
        ];
    }

    public function broadcastAs(): string
    {
        return 'OrderMatched';
    }
}
