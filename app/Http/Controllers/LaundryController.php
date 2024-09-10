<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Cache;
use App\Models\Laundry;
use App\Models\LaundryItem;
use App\Models\MySession;
use Carbon\Carbon;
use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Support\Facades\DB;
use Validator;
use Auth;
class LaundryController extends BaseController
{



    public function store(Request $request)
    {
        try {
            $validator =Validator::make($request->all(), [
                'name_en'=> 'required',
                'name_ar' => 'required',
                'description_ar' => 'required',
                'description_en'=> 'required|string',
                'phone_number' => 'required|string',
                'city' => 'required',
                'address_line_1'=> 'required',
                'address' => 'required',
                'lat' => 'required',
                'lng' => 'required',
                'point' => 'required',
                'admin_id'=>'required|exists:users,id',
            ]); 
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors()->all());       
            }
        
            $laundry = Laundry::create([
                'name_en' => $request->name_en,
                'name_ar'=> $request->name_ar,
                'description_ar' => $request->description_ar,
                'description_en' => $request->description_en,
                'phone_number' => $request->phone_number,
                'city' => $request->city,
                'address_line_1' => $request->address_line_1,
                'address' => $request->address,
                'point' => $request->point,
                'admin_id'=> $request->admin_id,
                'lat' => $request->lat,
                'lng' => $request->lng,

           ]);
            
        return $this->sendResponse($laundry,'laundry created successfully.');
    
    } catch (\Throwable $th) {
        return response()->json([
            'status' => false,
            'message' => $th->getMessage()
        ], 500); 
    
    } 


    }


   


 public function LaundryByAdmin()
    {
        try{
        $user = Auth::user();
        $Laundries = [];

        if ($user->user_type_id == 1) {
            $Laundries = Laundry::with(['LaundryMedia', 'LaundryItem'])
                ->where('admin_id', $user->id)
                ->paginate(10);
        } elseif ($user->user_type_id == 4) {
            $Laundries = Laundry::with(['LaundryMedia', 'LaundryItem'])
                ->paginate(10);
        }
        return $this->sendResponse($Laundries,'Laundries fetched successfully.');
    } catch (\Exception $e) {
        // Log error and return empty array
        return response()->json(['error' =>  $e->getMessage()], 500);
      
    }
    }

    public function index()
    {
        try{
        $Laundries =  Laundry::with(['LaundryMedia','LaundryItem'])
                           ->where('isActive', 1)
                            ->inRandomOrder()
                            ->get()
                            ->makeHidden(['isActive']);
    
    
        return $this->sendResponse($Laundries, 'Laundries fetched successfully.');
    } catch (\Exception $e) {
        // Log error and return empty array
        return response()->json(['error' =>  $e->getMessage()], 500);
      
    }
    }
    



    public function search(Request $request)
{
    try{
    $query = $request->input('name');

     $user = Auth::user();
     $latitude = $user->lat;  
     $longitude = $user->lng;

    // البحث عن المغاسل مع حساب المسافة
    $results = Laundry::where('isActive', 1)
    ->when($query, function ($queryBuilder) use ($query) {
        $queryBuilder->where(function ($q) use ($query) {
            $q->where('name_en', 'LIKE', '%' . $query . '%')
                ->orWhere('name_ar', 'LIKE', '%' . $query . '%');
        });
    })
    // حساب المسافة باستخدام صيغة Haversine
    ->selectRaw(
        "*, ( 6371 * acos( cos( radians(?) ) * cos( radians( lat ) ) 
        * cos( radians( lng ) - radians(?) ) + sin( radians(?) ) 
        * sin( radians( lat ) ) ) ) AS distance",
        [$latitude, $longitude, $latitude] // تمرير إحداثيات المستخدم إلى الصيغة
    )
    ->orderBy('distance', 'asc') // ترتيب النتائج بناءً على المسافة
    ->get()
    ->makeHidden('isActive');
    return $this->sendResponse($results, 'Laundries fetched successfully.');
} catch (\Exception $e) {
    // Log error and return empty array
    return response()->json(['error' =>  $e->getMessage()], 500);
  
}
}





public function show(Request $request)
{
    try{
    $validator =Validator::make($request->all(), [
        'id' => 'required|exists:laundries',
    
    ]);
   
    if($validator->fails()){
        return $this->sendError('Validation Error.', $validator->errors()->all());       
    }
      // Find the country by ID
    $laundry = Laundry::with(['LaundryMedia','LaundryItem','services'])->findOrFail($request->id);

     // Transform the result to include the address array
     $laundryData = [
        'id' => $laundry->id,
        'name_en' => $laundry->name_en,
        'name_ar' => $laundry->name_ar,
        'description_ar' => $laundry->description_ar,
        'description_en' => $laundry->description_en,
        'email' => $laundry->email,
        'phone_number' => $laundry->phone_number,
       
        'address' => [
            'city' => $laundry->city,
            'address_line_1' => $laundry->address_line_1,
            'lat' => $laundry->lat,
            'lng' => $laundry->lng,
        ],
        'services' => $laundry->services,
        'LaundryMedia' => $laundry->LaundryMedia,
        'LaundryItem' => $laundry->LaundryItem,
       
        // Add other fields as necessary
    ];

    return $this->sendResponse($laundryData,'laundry fetched successfully.');
} catch (\Exception $e) {
    // Handle any exceptions and return an error response
    return response()->json(['error' => $e->getMessage()], 500);
}
}


public function getLaundriesByProximity(Request $request)
{
    
    try {
        // Get the currently authenticated user
        $user = Auth::user();
    
        $userLatitude = $user->lat;
        $userLongitude = $user->lng;

        // Fetch laundries from the cache or database
        $laundries =Laundry::select(
                'id',
                'name_ar',
                'name_en',
                'description_ar',
                'description_en',
                'phone_number',
                'city',
                'address_line_1',
                'lat',
                'lng',
                DB::raw("( 6371 * acos( cos( radians($userLatitude) ) * cos( radians(lat) ) * cos( radians(lng) - radians($userLongitude) ) + sin( radians($userLatitude) ) * sin( radians(lat) ) ) ) AS distance")
            )
            ->orderBy('distance')
            ->where('isActive',1)
            ->with(['LaundryMedia','laundryItem']) // Eager load the laundryItem relationship
            ->get();
    

        return $this->sendResponse($laundries, 'Laundries fetched successfully.');
        
    } catch (\Exception $e) {
        // Handle any exceptions and return an error response
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

        
}
