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



public function sendNotification(Request $request, FirebaseService $firebaseService)
{
    $request->validate([
        'device_token' => 'required',
        'title' => 'required',
        'body' => 'required',
    ]);

    $notification = new Notification();
    $notification->title ='test';
    $notification->body = 'test Body';
    $notification->device_token = $request->device_token;
    $notification->save();

    $firebaseService->sendNotification(
        $request->device_token,
        $request->title,
        $request->body
    );

    return response()->json(['success' => true]);
}

}
