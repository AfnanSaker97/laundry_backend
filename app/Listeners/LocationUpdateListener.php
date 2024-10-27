namespace App\Listeners;

use App\Models\CarTracking; // تأكد من تضمين نموذج السيارة
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LocationUpdateListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle($message)
    {
        // تحقق مما إذا كانت الرسالة تحتوي على الحقول المطلوبة
        if (!isset($message['latitude'], $message['longitude'], $message['car_id'])) {
            Log::error('Message missing required fields: ' . json_encode($message));
            return;
        }

        $latitude = $message['latitude'];
        $longitude = $message['longitude'];
        $carId = $message['car_id'];

        // تحقق من وجود السيارة قبل التحديث
        if (CarTracking::where('car_id', $carId)->exists()) {
            // تحديث إحداثيات السيارة في قاعدة البيانات
            CarTracking::where('car_id', $carId)->update([
                'latitude' => $latitude,
                'longitude' => $longitude,
            ]);

            Log::info("Car ID: $carId updated with Latitude: $latitude, Longitude: $longitude");
        } else {
            Log::warning("Car ID: $carId does not exist in the database.");
        }
    }
}
