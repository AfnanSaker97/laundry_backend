<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Cache;
use App\Models\Laundry;
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
        $user = Auth::user();
        $Laundries = Laundry::with('prices')->where('admin_id', $user->id)->get();

  
        return $this->sendResponse($Laundries,'Laundries fetched successfully.');
    }


    public function index()
    {
        
        $Laundries = Cache::remember('Laundries', 60, function () {
            return Laundry::with('prices')->get();
        });
        return $this->sendResponse($Laundries,'Laundries fetched successfully.');
    }



    public function search(Request $request)
{
    $query = $request->input('name');

    // If the query is empty, return an empty array
    $results = $query 
        ? Laundry::where('name', 'LIKE', '%' . $query . '%')->get()
        : [];

    return $this->sendResponse($results, 'Laundries fetched successfully.');
}





public function show(Request $request)
{
    $validator =Validator::make($request->all(), [
        'id' => 'required|exists:laundries',
    
    ]);
   
    if($validator->fails()){
        return $this->sendError('Validation Error.', $validator->errors()->all());       
    }
      // Find the country by ID
    $laundry = Laundry::with('prices')->findOrFail($request->id);
    return $this->sendResponse($laundry,'laundry fetched successfully.');
}


public function getLaundriesByProximity(Request $request)
{
 
    try {
    $user = Auth::user();
    $userLatitude =  $user ->lat;
    $userLongitude =  $user ->lng;

    $laundries = DB::table('laundries')
        ->select(
            'id',
            'name_ar',
            'name_en',
            'city',
            'lat',
            'lng',
            DB::raw("( 6371 * acos( cos( radians($userLatitude) ) * cos( radians(lat) ) * cos( radians(lng) - radians($userLongitude) ) + sin( radians($userLatitude) ) * sin( radians(lat) ) ) ) AS distance")
        )
        ->orderBy('distance')
        ->get();

   
        return $this->sendResponse($laundries, 'Laundries fetched successfully.');
} catch (\Exception $e) {
    DB::rollBack();
    // Log error and return empty array
    return response()->json(['error' =>  $e->getMessage()], 500);
  
}
}
}
