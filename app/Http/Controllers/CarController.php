<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Cache;
use App\Models\Laundry;
use App\Models\Car;
use App\Models\MySession;
use Carbon\Carbon;
use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Exception\MessagingException;
use App\Models\Notification; // النموذج المستخدم لتخزين الإشعارات
use Illuminate\Support\Facades\Log;
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

public function sendPushNotification(Request $request)
    {
        $firebaseToken = User::whereNotNull('device_token')->pluck('device_token')->all();
          
        $SERVER_API_KEY = 'Enter_Your_Server_Key';
  
        $data = [
            "registration_ids" => $firebaseToken,
            "notification" => [
                "title" => $request->title,
                "body" => $request->body,  
            ]
        ];
        $dataString = json_encode($data);
    
        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];
    
        $ch = curl_init();
      
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
               
        $response = curl_exec($ch);
  
        dd($response);
    }


public function sendNotification(Request $request)
{

  
  try {
    $deviceToken="dDhpC6A4TZSVtQzXcuK9lJ:APA91bGoAYqkSANFmfy7bFi9iUpDxI0zF0U3MW45L3LMchWplct3kb2DKzoFcvFyoUqdncy9qMrvEnMMJlghi5AI0Rk9j9mN-9UpIdnSuQMKVvAzKmW5BlUiC8aKV1d9Ty3xOx58yCeW";
   // مسار ملف اعتمادات Firebase
   $firebaseCredentialsPath = storage_path('app/firebase_credentials.json');

   // تحقق من وجود الملف
   if (!file_exists($firebaseCredentialsPath)) {
       Log::error('Firebase credentials file not found');
       return response()->json(['error' => 'Firebase credentials file not found'], 500);
   }

   // تهيئة Firebase باستخدام ملف الاعتمادات
   $firebase = (new Factory)->withServiceAccount($firebaseCredentialsPath);
   $messaging = $firebase->createMessaging();

  // Set up notification data
  $notificationData = [
    'title' => 'Order Update',
    'body' => 'Your order has been received successfully',
    'order_id' => $order_id  // Include the order ID in the notification
];

   $message = CloudMessage::withTarget('token', $deviceToken)
       ->withNotification($notificationData)
       ->withData([
        'order_id' => $order_id, 
       ]);

   // إرسال الرسالة عبر Firebase
     $messaging->send($message);

   // تخزين الإشعار في قاعدة البيانات
   Notification::create([
       'type' => 'OrderNotification',
       'notifiable_id' => 1,  // معرف المستخدم الذي تم إرسال الإشعار له
       'data' => json_encode($notificationData),  // تخزين البيانات كـ JSON
   ]);

   return response()->json(['message' => 'Notification sent and stored successfully'], 200);

} catch (MessagingException $e) {
   // سجل أي أخطاء متعلقة بـ Firebase Messaging
   Log::error('Firebase Messaging Error: ' . json_encode($e->errors()));
   return response()->json(['error' => 'Failed to send notification'], 500);
} catch (\Exception $e) {
   // سجل أي أخطاء عامة
   Log::error('General Error: ' . $e->getMessage());
   return response()->json(['error' => 'An error occurred while sending notification'], 500);
}
}
}