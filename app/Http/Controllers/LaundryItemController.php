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
    public function index(Request $request)
    {
        $validator =Validator::make($request->all(), [

            'laundry_id' => 'required|exists:laundries,id',
        ]);
       
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors()->all());       
        }
        $laundry = Laundry::findOrFail($request->laundry_id);

        try {
        // Retrieve the laundry items with their prices from the pivot table
        $laundryItems = Cache::remember('laundryItems_'.$request->laundry_id, 60, function () use ($laundry) {
            return $laundry->LaundryItem()->withPivot('price')->get();
        });

        return $this->sendResponse($laundryItems,'LaundryItem fetched successfully.');
    } catch (\Exception $e) {
        // Log error and return empty array
        return response()->json(['error' =>  $e->getMessage()], 500);
      
    }
    }


    public function getLaundryItem(Request $request)
    {
      
        try {
        // Retrieve the laundry items with their prices from the pivot table
        $laundryItems = Cache::remember('laundryItems', 60, function() {
            return LaundryItem::all();
        });

        return $this->sendResponse($laundryItems,'laundryItems fetched successfully.');
    } catch (\Exception $e) {
        // Log error and return empty array
        return response()->json(['error' =>  $e->getMessage()], 500);
      
    }
    }


    public function show(Request $request)
{
    $validator = Validator::make($request->all(), [
        'laundry_id' => 'required|exists:laundries,id',
        'item_id' => 'required|exists:laundry_items,id',  
    ]);

    if ($validator->fails()) {
        return $this->sendError('Validation Error.', $validator->errors()->all());
    }

    try {
        // Find the laundry
        $laundry = Laundry::findOrFail($request->laundry_id);

        $laundryItem = Cache::remember('laundryItem_'.$request->laundry_id.'_'.$request->item_id, 60, function () use ($laundry, $request) {
            return $laundry->LaundryItem()
                ->where('laundry_items.id', $request->item_id)  // تحديد مصدر id لتجنب الغموض
                ->withPivot('price')
                ->first();
        });
        if (!$laundryItem) {
            return $this->sendError('Item not found.');
        }

        return $this->sendResponse($laundryItem, 'Laundry item fetched successfully.');
    } catch (\Exception $e) {
        // Log error and return empty array
        return response()->json(['error' => $e->getMessage()], 500);
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
          // حذف الكاش المرتبط بالعناصر المغسلة
         Cache::forget('laundryItems_' . $request->laundry_id);

   
  
        return $this->sendResponse($laundry,'Laundry Price updated successfully.');

    } catch (\Exception $e) {
        // Log error and return empty array
        return response()->json(['error' =>  $e->getMessage()], 500);
      
    }    }

}
