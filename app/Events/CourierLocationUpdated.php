<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CourierLocationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param string $orderId
     * @param float $latitude
     * @param float $longitude
     * @param string|null $courierName
     * @param string|null $updatedAt
     */
    public function __construct(
        public string $orderId,
        public float $latitude,
        public float $longitude,
        public ?string $courierName = null,
        public ?string $updatedAt = null
    ) {
        $this->updatedAt = $updatedAt ?? now()->toIso8601String();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('orders.' . $this->orderId),
            new Channel('admin-tracking'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'CourierLocationUpdated';
    }

    /**
     * Data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->orderId,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'courier_name' => $this->courierName,
            'updated_at' => $this->updatedAt,
        ];
    }
}

