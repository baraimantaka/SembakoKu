<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewOrderCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    /**
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('store-admin'),
            new Channel('courier-tasks'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'NewOrderCreated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->order->id,
            'invoice_no' => $this->order->invoice_no,
            'total_price' => $this->order->total_price,
            'formatted_total' => $this->order->formatted_total,
            'payment_method' => $this->order->payment_method,
            'customer_name' => $this->order->customer?->name ?? 'Pelanggan',
            'created_at' => $this->order->created_at->format('H:i'),
        ];
    }
}

