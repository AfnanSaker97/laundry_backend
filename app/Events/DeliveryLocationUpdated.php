<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryLocationUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $carId;
    public $latitude;
    public $longitude;
    /**
     * Create a new event instance.
     */
    public function __construct($carId, $latitude, $longitude)
    {
        $this->carId = $carId;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('delivery-tracking'),
        ];
    }

    public function broadcastAs()
    {
        return 'location-updated';
    }

}
