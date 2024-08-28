<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Cache;
use App\Models\Laundry;
use App\Models\Car;
use App\Models\Notification;
use App\Models\MySession;
use Carbon\Carbon;
use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

use Validator;
use Auth;
class CarController extends BaseController
{
    
public function index(Request $request)
{
    $validator =Validator::make($request->all(), [
        'laundry_id' => 'required|exists:laundries,id',
    
    ]);
   
    if($validator->fails()){
        return $this->sendError('Validation Error.', $validator->errors()->all());       
    }
      // استخدام Eager Loading لتحميل العلاقة مع السائق
      $cars = Car::with('driver') // تحميل السائق مع كل سيارة
      ->where('laundry_id', $request->laundry_id)
      ->get();

    return $this->sendResponse($cars,'car fetched successfully.');
}



public function sendNotification(Request $request)
{

    try{
   // استرداد device_token من قاعدة البيانات أو من الطلب
  //  $deviceToken = $request->device_token;

  $path = storage_path('app/firebase_credentials.json');

// تحقق من أن المسار صحيح وأن الملف موجود
if (!file_exists($path)) {
    return response()->json(['error' => 'Firebase credentials file not found'], 500);
}

    $firebase = (new Factory)->withServiceAccount($path);
    $messaging = $firebase->createMessaging();

    $message = CloudMessage::fromArray([
        'notification' => [
            'title' => 'test',
            'body' => 'test',
        ],
        'token' =>'fkgjfdggigf',
    ]);

    $messaging->send($message);

    return response()->json(['message' => 'Notification sent successfully']);

   
} catch (MessagingException $e) {
    // سجلات Firebase للتفاصيل
    \Log::error('Firebase Messaging Error: ' . $e->errors());

    return response()->json(['error' => $e->getMessage()], 500);
} catch (\Exception $e) {
    // سجل أي أخطاء أخرى
    \Log::error('General Error: ' . $e->getMessage());

    return response()->json(['error' => $e->getMessage()], 500);
}
}

}
