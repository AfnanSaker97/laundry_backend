<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LaundryItem;
use App\Models\Price;
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





    public function store(Request $request)
    {
        try {
        $validator =Validator::make($request->all(), [
            'item_type_en' => 'required|string|unique:laundry_items,item_type_en',
            'item_type_ar' => 'required|string|unique:laundry_items,item_type_ar',
            'url_image' => 'required|file',
        ]);
       
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors()->all());       
        }
      
           $image =$request->file('url_image');
           $imageName = time() . '_' . uniqid() . '.' . $image->extension();
           $image->move(public_path('LaundryItem'), $imageName);
           $url = url('LaundryItem/' . $imageName);
       
           $laundryItem = LaundryItem::create([
            'item_type_en' => $request->item_type_en,
            'item_type_ar' => $request->item_type_ar,
            'url_image' => $url,
        ]);

      
         Cache::forget('laundryItems');
        return $this->sendResponse($laundryItem,'Laundry Item added successfully.');

    } catch (\Exception $e) {
     
        return response()->json(['error' =>  $e->getMessage()], 500);
      
    }    }


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



    public function showLaundryItem(Request $request)
    {
        $validator = Validator::make($request->all(), [
           'id' => 'required|exists:laundry_items,id',  
        ]);
    
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());
        }
    
        try {
           
            $laundryItem = LaundryItem::findOrFail($request->id);
            return $this->sendResponse($laundryItem, 'Laundry item fetched successfully.');
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
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




public function UpdateItem(Request $request)
{
    try {
    
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:laundry_items,id',
            'item_type_en' => 'nullable|string|unique:laundry_items,item_type_en,' . $request->id,
            'item_type_ar' => 'nullable|string|unique:laundry_items,item_type_ar,' . $request->id,
            'url_image' => 'nullable|file',
        ]);
   
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());
        }

        $laundryItem = LaundryItem::findOrFail($request->id);

        $image = $request->file('url_image');
        $imageName = time() . '_' . uniqid() . '.' . $image->extension();
        $image->move(public_path('LaundryItem'), $imageName);
        $url = url('LaundryItem/' . $imageName);

      
        $laundryItem->update([
            'item_type_en' => $request->item_type_en,
            'item_type_ar' => $request->item_type_ar,
            'url_image' => $url,
        ]);
        Cache::forget('laundryItems');
        return $this->sendResponse($laundryItem, 'Laundry Item updated successfully.');

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}





public function deleteItem(Request $request)
{
    try {
    
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:laundry_items,id',
        ]);
   
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());
        }

        $laundryItem = LaundryItem::findOrFail($request->id);
        if ($laundryItem->url_image) {
            $imagePath = public_path(parse_url($laundryItem->url_image, PHP_URL_PATH));
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        $laundryItem->delete();
    
        Cache::forget('laundryItems');
        return $this->sendResponse($laundryItem, 'Laundry Item deleted successfully.');

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}




public function update(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:laundry_items,id',
            'laundry_id' => 'required|exists:laundries,id',
            'order_type_id' => 'required|exists:order_types,id',
            'service_id' => 'required|exists:services,id',
            'price' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());
        }

        $updated = Price::where([
            'laundry_id' => $request->laundry_id,
            'laundry_item_id' => $request->id,
            'order_type_id' => $request->order_type_id,
            'service_id' => $request->service_id,
        ])->first();
        $updated->price = $request->price;
        $updated->save();
        
        if (!$updated) {
            return $this->sendError('No matching record found to update.', []);
        }
        Cache::forget('laundryItems_' . $request->laundry_id);

        return $this->sendResponse($updated, 'Laundry price updated successfully.');
    } catch (\Exception $e) {

        return response()->json(['error' => $e->getMessage()], 500);
    }
}


}
