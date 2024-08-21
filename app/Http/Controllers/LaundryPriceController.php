<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LaundryPrice;
use Carbon\Carbon;
use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Validator;
use Auth;
class LaundryPriceController extends BaseController
{


    public function index()
    {
      
        $LaundryPrice = Cache::remember('LaundryPrice', 60, function () {
            return LaundryPrice::all();
        });
        return $this->sendResponse($LaundryPrice,'LaundryPrice fetched successfully.');
  
    }
    public function update(Request $request)
{
 
    $validator =Validator::make($request->all(), [
        'id' => 'required|exists:laundry_prices',
    
    ]);
   
    if($validator->fails()){
        return $this->sendError('Validation Error.', $validator->errors()->all());       
    }
      // Find the country by ID
    $laundryPrice = LaundryPrice::findOrFail($request->id);

    $laundryPrice->price = $request->price;
    $laundryPrice->save();
    return $this->sendResponse($laundryPrice,'Laundry Price updated successfully.');
}

}
