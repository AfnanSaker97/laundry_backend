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

 public function LaundryByAdmin()
    {
        try{
        $user = Auth::user();
        $Laundries = Laundry::with(['LaundryMedia','laundryItem'])->where('admin_id', $user->id)->get();
    
        return $this->sendResponse($Laundries,'Laundries fetched successfully.');
    } catch (\Exception $e) {
        // Log error and return empty array
        return response()->json(['error' =>  $e->getMessage()], 500);
      
    }
    }

    public function index()
    {
        try{
        $Laundries =  Laundry::with(['LaundryMedia','LaundryItem'])->inRandomOrder()->get();
    
    
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
    $results = $query 
    ? Laundry::where(function($queryBuilder) use ($query) {
        $queryBuilder->where('name_en', 'LIKE', '%' . $query . '%')
                     ->orWhere('name_ar', 'LIKE', '%' . $query . '%');
    })->get()
    : [];

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
            ->with(['LaundryMedia','laundryItem']) // Eager load the laundryItem relationship
            ->get();
    

        return $this->sendResponse($laundries, 'Laundries fetched successfully.');
        
    } catch (\Exception $e) {
        // Handle any exceptions and return an error response
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

        
}
