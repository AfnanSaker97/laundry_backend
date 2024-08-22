<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LaundryItem;
use App\Models\Laundry;
use Carbon\Carbon;
use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Validator;
use Auth;
class LaundryItemController extends BaseController
{
    public function index()
    {
        try {
        $LaundryItem = Cache::remember('LaundryItem', 60, function () {
            return LaundryItem::all();
        });
        return $this->sendResponse($LaundryItem,'LaundryItem fetched successfully.');
    } catch (\Exception $e) {
        // Log error and return empty array
        return response()->json(['error' =>  $e->getMessage()], 500);
      
    }
    }


    public function update(Request $request)
    {
        try {
        $validator =Validator::make($request->all(), [
            'id' => 'required|exists:laundry_items',
            'laundry_id' => 'required|exists:laundries,id',
            'price' => 'required',
        ]);
       
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors()->all());       
        }
        // Find the laundry
        $laundry = Laundry::findOrFail($request->laundry_id);

           // Update the price in the pivot table
          $laundry->LaundryItem()->updateExistingPivot($request->id, ['price' => $request->price]);

   
  
        return $this->sendResponse($laundry,'Laundry Price updated successfully.');

    } catch (\Exception $e) {
        // Log error and return empty array
        return response()->json(['error' =>  $e->getMessage()], 500);
      
    }    }

}
