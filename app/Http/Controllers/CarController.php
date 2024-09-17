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




public function store(Request $request)
{
    try {
        $validator =Validator::make($request->all(), [
            'laundry_id'=> 'required|exists:laundries,id',
            'driver_id' => 'required|exists:users,id',
            'number_car' => 'required',
            
           
        ]); 
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());       
        }
    
        $car = Car::create([
            'laundry_id' => $request->laundry_id,
            'driver_id'=> $request->driver_id,
            'number_car' => $request->number_car,
          
       ]);
        
    return $this->sendResponse($car,'Car created successfully.');

} catch (\Throwable $th) {
    return response()->json([
        'status' => false,
        'message' => $th->getMessage()
    ], 500); 

} 


}

    
public function getCars()
{
      $cars = Car::with('driver','Laundry')
      ->paginate(10);

      $filteredCars = $cars->map(function($car) {
        return [
            'id' => $car->id,
          //  'driver_phone' => $car->driver->phone, // تأكد أن عمود الهاتف موجود في جدول السائق
            'status' => $car->status,
            'number_car'=> $car->number_car,
            'lat' => $car->lat,
            'lng' => $car->lng,
            'driver_name' => $car->driver->name,
            'driver_id' => $car->driver->id,
            'laundry_name_ar' => $car->Laundry->name_ar,
            'laundry_name_en' => $car->Laundry->name_en,
            'laundry_phone_number' => $car->Laundry->phone_number,
            'laundry_name_ar' => $car->Laundry->name_ar,
        ];
    });

    return $this->sendResponse($filteredCars,'car fetched successfully.');
}



public function show(Request $request)
     {
       try {
         $validator =Validator::make($request->all(), [
             'id' => 'required|exists:cars',
         ]); 
     
         if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());
        }
           $car =Car::with('driver','Laundry')->findOrFail($request->id);
         
    $filteredCar = [
        'id' => $car->id,
        'status' => $car->status,
        'number_car'=> $car->number_car,
        'lat' => $car->lat,
        'lng' => $car->lng,
        'driver_name' => $car->driver->name,
        'driver_id' => $car->driver->id,
        'laundry_name_ar' => $car->Laundry->name_ar,
        'laundry_name_en' => $car->Laundry->name_en,
        'laundry_phone_number' => $car->Laundry->phone_number,
    ];

             return $this->sendResponse($filteredCar,'Car updated successfully.');
         } catch (\Throwable $th) {
             return response()->json([
                 'status' => false,
                 'message' => $th->getMessage()
             ], 500); 
         
         } 
     }
    
}