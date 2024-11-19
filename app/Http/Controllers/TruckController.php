<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
class TruckController extends Controller
{
    protected $database;

    public function __construct()
    {
        
        try {
      Log::info('Test log: Constructor is being initialized');

        $firebaseCredentialsPath = storage_path('app/firebase_credentials.json');
        if (!file_exists($firebaseCredentialsPath)) {
    throw new \Exception('Firebase credentials file not found at: ' . $firebaseCredentialsPath);
}

Log::info('Firebase credentials path: ' . $firebaseCredentialsPath);

    
    $factory = (new Factory)
    ->withServiceAccount($firebaseCredentialsPath)
    ->withDatabaseUri('https://dopepro-7aae6-default-rtdb.firebaseio.com/');


    $this->database = $factory->createDatabase();
        } catch (\Exception $e) {
            // في حال حدوث خطأ أثناء الاتصال بـ Firebase
            Log::error('Error initializing Firebase: ' . $e->getMessage());
            return response()->json(['error' => 'Error initializing Firebase'], 500); // إرجاع استجابة HTTP في حالة فشل الاتصال
        }
}



public function updateLocation(Request $request, $truckId)
{
    try {
       /* $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);*/
       /* $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');
*/
$latitude = mt_rand(-90 * 1000000, 90 * 1000000) / 1000000;  
$longitude = mt_rand(-180 * 1000000, 180 * 1000000) / 1000000;  

        if (!$latitude || !$longitude) {
            return response()->json(['error' => 'Invalid coordinates'], 400);
        }

        
        $this->database->getReference('car/' . $truckId)->set([
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);
        DB::table('car_trackings')->updateOrInsert(
            ['car_id' => $truckId],
            [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'updated_at' => now(),
            ]
        );
      

        return response()->json(['status' => 'Location updated successfully'], 200);
    } catch (\Exception $e) {
        Log::error('Error updating location: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
}
