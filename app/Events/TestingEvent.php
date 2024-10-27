<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
class TestingEvent implements ShouldBroadcast

{    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */

   //  public $userId;
     public $carId;
     public $latitude;
     public $longitude;

     public function __construct($carId, $latitude, $longitude)
     {
     
        // $this->userId = $userId;
         $this->carId = $carId;
         $this->latitude = $latitude;
         $this->longitude = $longitude;
     }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        try {
            return new Channel('user');
        } catch (\Exception $e) {
            Log::error('BroadcastOn failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function broadcastWith()
    {
        return [
            'car_id' => $this->carId,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'timestamp' => now()->toDateTimeString(),
        ];
    }

    
    public function broadcastAs()
    {
        return 'TestingEvent'; // هذا هو اسم الحدث الذي تبحث عنه
    }
    
}
