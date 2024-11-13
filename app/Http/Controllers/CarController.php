<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Cache;
use App\Models\Laundry;
use App\Models\Order;
use App\Models\Car;
use App\Models\CarTracking;
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

use App\Jobs\UpdateCarLocation;
class CarController extends BaseController
{


    function periodicallyUpdateLocation(Request $request) {
        try{
        $validator =Validator::make($request->all(), [
            'car_id' => 'required|exists:cars,id'
        
        ]);
        $carId =$request->car_id;

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors()->all());       
        }
        $carId = $request->car_id;

        // Dispatch jobs with a delay to update location every 5 seconds
        for ($i = 0; $i < 12; $i++) { // Run for 1 minute as an example
            UpdateCarLocation::dispatch($carId)->delay(now()->addSeconds($i * 5));
        }
     return $this->sendResponse('','car fetched successfully.');
          }  catch (\Throwable $th) {
        return response()->json([
            'status' => false,
            'message' => $th->getMessage()
        ], 500); 

    }
    
}
public function index(Request $request)
{
    // Validate the input
    $validator = Validator::make($request->all(), [
        'page' => 'nullable|integer',
        'laundry_id'=>'nullable|exists:laundries,id',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $user = Auth::user();
    $query = Car::with('driver','Laundry');
    if ($user->user_type_id != 1 && $user->user_type_id != 4) {
        return $this->sendError('Access Denied.');
    }
  if ($user->user_type_id == 1) {
        $query->where('laundry_id', $user->laundry->id);
    }

    if ($user->user_type_id == 4 && $request->has('laundry_id')) {
        $query->where('laundry_id', $user->laundry->id);
    }

    if ($request->has('search')) {
        $query->where('number_car', 'like', '%' . $request->search . '%');
    }

    if ($request->has('page') && $request->page == 0) {
        $cars = $query->get(); 
    } else {
        $cars = $query->paginate(10);
    }
    return $this->sendResponse($cars, 'Cars fetched successfully.');
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

public function update(Request $request)
{
    try {
        $validator =Validator::make($request->all(), [
            'id'=> 'nullable|exists:cars',
            'laundry_id'=> 'nullable|exists:laundries,id',
            'driver_id' => 'nullable|exists:users,id',
            'number_car' => 'nullable',
                
        ]); 
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());       
        }
       $car =Car::findOrFail($request->id);
          $car->update([
            'laundry_id' => $request->laundry_id ?? $car->laundry_id,
            'driver_id' => $request->driver_id ?? $car->driver_id,
            'number_car' =>$request->number_car ?? $car->number_car,
        ]);
    
    return $this->sendResponse($car,'Car updated successfully.');

} catch (\Throwable $th) {
    return response()->json([
        'status' => false,
        'message' => $th->getMessage()
    ], 500); 

} 
}



public function UpdateStatusCar(Request $request)
{
   
    try {

        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:cars', 
        ]);
       
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());
        }
        $car = Car::findOrFail($request->id);
      
        $car->status = !$car->status;  // If 1, it becomes 0, and vice versa
        $car->save();
        return $this->sendResponse($car,'car updated successfully.');
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

    $response = [
        'cars' => $filteredCars,
        'pagination' => [
            'current_page' => $cars->currentPage(),
            'per_page' => $cars->perPage(),
            'total' => $cars->total(),
            'last_page' => $cars->lastPage(),
            'has_more_pages' => $cars->hasMorePages(),
            'from' => $cars->firstItem(), // First item number on the current page
            'to' => $cars->lastItem(),   // Last item number on the current page
       
        ]
    ];
    return $this->sendResponse($response,'car fetched successfully.');
}



public function search(Request $request)
{
   
    $validator = Validator::make($request->all(), [
        'number' => 'nullable|string', 
    ]);
    if ($validator->fails()) {
        return $this->sendError('Validation Error.', $validator->errors()->all());
    }
    $query = Car::with('driver','Laundry');
    if ($request->has('number')) {
        $query->where('number_car', 'like', '%' . $request->number . '%');
        }
        $cars = $query->paginate(10);
        $filteredCars = $cars->map(function($car) {
            return [
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
                'laundry_name_ar' => $car->Laundry->name_ar,
            ];
        });
    
        $response = [
            'cars' => $filteredCars,
            'pagination' => [
                'current_page' => $cars->currentPage(),
                'per_page' => $cars->perPage(),
                'total' => $cars->total(),
                'last_page' => $cars->lastPage(),
                'has_more_pages' => $cars->hasMorePages(),
                'from' => $cars->firstItem(), // First item number on the current page
                'to' => $cars->lastItem(),   // Last item number on the current page
           
            ]
        ];
        return $this->sendResponse($response,'car fetched successfully.');
 
    
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
        'laundry_id' => $car->Laundry->id,
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


     public function updateCoordinates(Request $request)
     {
        $validator =Validator::make($request->all(), [
             'car_id' => 'required|exists:cars,id',
             'latitude' => 'required|numeric',
             'longitude' => 'required|numeric',
         ]);
 
         if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());
        }
         try {
             $carId = $request->input('car_id');
             $latitude = $request->input('latitude');
             $longitude = $request->input('longitude');

              // البحث عن إحداثيات السيارة في قاعدة البيانات
        $carCoordinate = CarTracking::where('car_id', $carId)->first();

        if ($carCoordinate) {
            // إذا كانت الإحداثيات موجودة، يتم تحديثها
            $carCoordinate->update([
                'latitude' => $latitude,
                'longitude' => $longitude,
            ]);
        } else {
   
            CarTracking::create([
                'car_id' => $carId,
                'latitude' => $latitude,
                'longitude' => $longitude,
            ]);
        }
            $orders =Order::where('car_id',$request->car_id)->where('status','Confirmed')->get();
        
      
      
            event(new TestingEvent($carId, $latitude, $longitude));
        
             // Optionally, log the update
             Log::info("Car {$carId} coordinates updated to Latitude: {$latitude}, Longitude: {$longitude}");
 
             return response()->json(['status' => 'Coordinates updated successfully'], 200);
         } catch (\Exception $e) {
             Log::error('Failed to update car coordinates: ' . $e->getMessage());
 
             return response()->json([
                 'status' => 'Error occurred',
                 'message' => $e->getMessage()
             ], 500);
         }
     }
    
}