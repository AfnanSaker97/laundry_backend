<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Support\Facades\DB;
use Validator;
use Auth;

class NotificationController extends BaseController
{
    public function markAsRead(Request $request)
    {
     
        // تحقق من صحة البيانات الواردة
        $validator = Validator::make($request->all(), [
            'notification_id' => 'required|exists:notifications,id',
        ]);
    
        // إذا فشل التحقق، أعد رسالة خطأ
        if ($validator->fails()) {
            return response()->json(['error' => 'Validation Error.', 'messages' => $validator->errors()->all()], 400);
        }
    
        // الحصول على الإشعار باستخدام ID
        $notification = Notification::find($request->notification_id);
    
        if ($notification) {
            // تحديث حالة القراءة
            $notification->update(['read_at' => now()]);
            return $this->sendResponse($notification,'Notification marked as read successfully.');

        }
    
        return response()->json(['error' => 'Notification not found'], 404);
    }



    public function getNotificationsForUser()
{
    try {
    $userId = Auth::id();
  
    $notifications = Notification::where('receiver_id', $userId)->orderBy('created_at', 'desc')->get();
 
    return $this->sendResponse($notifications,'notification fetched successfully.');
} catch (\Throwable $th) {
    return response()->json([
        'status' => false,
        'message' => $th->getMessage()
    ], 500); 

} 
}
}
