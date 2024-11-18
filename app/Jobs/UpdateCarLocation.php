<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Events\DeliveryLocationUpdated;
use Illuminate\Support\Facades\Log;
class UpdateCarLocation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $carId;

    public function __construct($carId)
    {
        $this->carId = $carId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $latitude = rand(24000000, 24780000) / 1000000;
        $longitude = rand(46700000, 47000000) / 1000000;

        \Log::info("Broadcasting location: carId {$this->carId}, lat: {$latitude}, long: {$longitude}");

        // Broadcast the new location
        broadcast(new DeliveryLocationUpdated($this->carId, $latitude, $longitude));

        // Update the database with the new location
        DB::table('car_trackings')->updateOrInsert(
            ['car_id' => $this->carId],
            [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'updated_at' => now(),
            ]
        );
    }
    
}
