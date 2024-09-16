<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Exception\MessagingException;
use App\Models\Notification; // النموذج المستخدم لتخزين الإشعارات
use Illuminate\Support\Facades\Log;
use Validator;
use Auth;

class BaseController extends Controller
{
    public function sendResponse($result, $message)
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];
  
        return response()->json($response, 200);
    }


    public function sendError($error, $errorMessages = [], $code = 422)
    {
        $response = [
            'success' => false,
            'errors' => $error,
        ];
  
        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }
  
        return response()->json($response, $code);
    }



    
    public function sendNotification(Request $request, $user, array $notificationContent)
    {
        try {
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
    
            // إعداد بيانات الإشعار
            $notificationData = array_merge([
                'title' => $notificationContent['title'] ?? 'Default Title',
                'body' => $notificationContent['body'] ?? 'Default body message',
                'order_id' => $notificationContent['order_id'] ?? null  // Include the order ID if provided
            ], $notificationContent);
    
            // إنشاء الرسالة
            $message = CloudMessage::withTarget('token', $user->device_token)
                ->withNotification([
                    'title' => $notificationData['title'],
                    'body' => $notificationData['body'],
                ])
                ->withData([
                    'order_id' => $notificationData['order_id'],
                ]);
    
            // إرسال الرسالة عبر Firebase
            $messaging->send($message);
    
            // تخزين الإشعار في قاعدة البيانات
            Notification::create([
                'type' => 'OrderNotification',
                'notifiable_id' => $user->id,
                'data' => json_encode($notificationData),
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
