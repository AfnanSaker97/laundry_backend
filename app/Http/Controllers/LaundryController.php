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
use Validator;
class LaundryController extends BaseController
{

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

}
