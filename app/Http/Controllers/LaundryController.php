<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Cache;
use App\Models\Laundry;
use App\Models\LaundryItem;
use App\Models\LaundryMedia;
use App\Models\AddressLaundry;
use App\Models\Price;
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
               // 'point' => 'required',
                'admin_id'=>'required|exists:users,id',
                "array_url.*.url_image" => 'required|file|mimes:jpg,png,jpeg,gif,svg,HEIF,BMP,webp|max:1500',
                'array_ids.*.laundry_item_id' => 'required|exists:laundry_items,id',
                'array_ids.*.price' => 'required|numeric',
                'array_ids' => 'required|array',
                'array_ids.*.service_id' => 'required|exists:services,id',
                'city' => 'required|string',
                'address_line_1' => 'required|string',
                'lat' => 'required|numeric',
                'lng' => 'required|numeric',
             ]); 
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors()->all());       
            }
          
            $laundryData = $request->only(['name_en', 'name_ar', 'description_ar', 'description_en', 'phone_number', 'admin_id']);
            $laundry = Laundry::create($laundryData);
    
            $imagesData = [];
            foreach ($request->file('array_url.*.url_image') as $image) {
                $imageName = time() . '_' . uniqid() . '.' . $image->extension();
                $image->move(public_path('Laundry'), $imageName);
                $imagesData[] = [
                    'laundry_id' => $laundry->id,
                    'url_image' => url('Laundry/' . $imageName),
                ];
            }
            LaundryMedia::insert($imagesData);

            $pricesData = array_map(function ($item) use ($laundry) {
                return [
                    'laundry_id' => $laundry->id,
                    'laundry_item_id' => $item['laundry_item_id'],
                    'service_id' => $item['service_id'],
                    'price' => $item['price'],
                ];
            }, $request->array_ids);
            Price::insert($pricesData);
          
            $addressesData = AddressLaundry::create([
                'laundry_id' => $laundry->id,
                'city' => $request->city,
                'address_line_1' => $request->address_line_1,
                'lat' => $request->lat ,
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


   


 public function LaundryByAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
           'page' => 'nullable|boolean' 
        ]);
    
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());
        }
        try{
        $user = Auth::user();
        $Laundries = [];

        if ($user->user_type_id == 1) {
            $query = Laundry::with(['LaundryMedia', 'LaundryItem','addresses'])
                ->where('admin_id', $user->id);
        } elseif ($user->user_type_id == 4) {
            $query = Laundry::with(['LaundryMedia', 'LaundryItem','addresses']);
        }

        if ($request->has('page') && $request->page == 0) {
            $Laundries = $query->get(); 
        } else {
            $Laundries = $query->paginate(10);
        }
        return $this->sendResponse($Laundries,'Laundries fetched successfully.');
        
    
    } catch (\Exception $e) {
        // Log error and return empty array
        return response()->json(['error' =>  $e->getMessage()], 500);
      
    }
    }




    public function update(Request $request)
    {
        try {
            // Validate the incoming request
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:laundries,id',
                'name_en' => 'required',
                'name_ar' => 'required',
                'description_ar' => 'required',
                'description_en' => 'required|string',
                'phone_number' => 'required|string',
             //   'admin_id' => 'required|exists:users,id',
                "array_url.*.url_image" => 'sometimes|required|file|mimes:jpg,png,jpeg,gif,svg,HEIF,BMP,webp|max:1500',
                'array_ids.*.laundry_item_id' => 'required|exists:laundry_items,id',
                'array_ids.*.price' => 'required|numeric',
                'array_ids' => 'required|array',
                'array_ids.*.service_id' => 'required|exists:services,id',
                'city' => 'required|string',
                'address_line_1' => 'required|string',
                'lat' => 'required|numeric',
                'lng' => 'required|numeric',
            ]);
    
            // Check for validation errors
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors()->all());
            }
    
            // Find the existing laundry record
            $laundry = Laundry::findOrFail($request->id);
    
            // Update the laundry data
            $laundryData = $request->only(['name_en', 'name_ar', 'description_ar', 'description_en', 'phone_number']);
            $laundry->update($laundryData);
    
            // Handle image uploads
            if ($request->hasFile('array_url')) {
                // Remove existing images if needed (optional)
                LaundryMedia::where('laundry_id', $laundry->id)->delete();
    
                $imagesData = [];
                foreach ($request->file('array_url') as $image) {
                    $imageName = time() . '_' . uniqid() . '.' . $image->extension();
                    $image->move(public_path('Laundry'), $imageName);
                    $imagesData[] = [
                        'laundry_id' => $laundry->id,
                        'url_image' => url('Laundry/' . $imageName),
                    ];
                }
                LaundryMedia::insert($imagesData);
            }
    
            // Update prices
            Price::where('laundry_id', $laundry->id)->delete(); // Remove old prices
            $pricesData = array_map(function ($item) use ($laundry) {
                return [
                    'laundry_id' => $laundry->id,
                    'laundry_item_id' => $item['laundry_item_id'],
                    'service_id' => $item['service_id'],
                    'price' => $item['price'],
                ];
            }, $request->array_ids);
            Price::insert($pricesData);
            $addressesData = AddressLaundry::create([
                'laundry_id' => $laundry->id,
                'city' => $request->city,
                'address_line_1' => $request->address_line_1,
                'lat' => $request->lat ,
                'lng' => $request->lng,
             
            ]);

    
            // Return a success response
            return $this->sendResponse($laundry, 'Laundry updated successfully.');
    
        } catch (\Throwable $th) {
            // Handle exceptions and return a response
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
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
        try {
            $query = $request->input('name');
            $user = Auth::user();
            $latitude = $user->lat;
            $longitude = $user->lng;
    
            $results = Laundry::with(['addresses']) 
            ->where('isActive', 1)
            ->when($query, function ($queryBuilder) use ($query) {
                $queryBuilder->where(function ($q) use ($query) {
                    $q->where('name_en', 'LIKE', '%' . $query . '%')
                      ->orWhere('name_ar', 'LIKE', '%' . $query . '%');
                });
            })
            ->get();
           
    
            return $this->sendResponse($results, 'Laundries fetched successfully.');
        } catch (\Exception $e) {
            return response()->json(['error' =>  $e->getMessage()], 500);
        }
    }
    





public function show(Request $request)
{
    try{
    $validator =Validator::make($request->all(), [
        'id' => 'required|exists:laundries',
    
    ]);
    $user = Auth::user();
    if($validator->fails()){
        return $this->sendError('Validation Error.', $validator->errors()->all());       
    }
      // Find the country by ID
    $laundry = Laundry::with(['LaundryMedia','LaundryItem','services','addresses','advertisement'])->findOrFail($request->id);

  
    $laundryData = $laundry->only([
        'id', 'name_en', 'name_ar', 'description_ar', 'description_en', 'email', 'phone_number'
    ]);
    $laundryData['addresses'] = $laundry->addresses;
    $laundryData['services'] = $laundry->services->groupBy('id')->map(function ($serviceGroup) {
        $service = $serviceGroup->first();
        $service->prices = $serviceGroup->pluck('pivot.price'); // جلب جميع الأسعار المرتبطة بكل خدمة
        return $service;
    })->values();

    // معالجة العناصر لتجنب التكرار
    $laundryData['LaundryItem'] = $laundry->LaundryItem->groupBy('id')->map(function ($itemGroup) {
        $item = $itemGroup->first();
        $item->prices = $itemGroup->pluck('pivot.price'); // جلب جميع الأسعار المرتبطة بكل عنصر
        return $item;
    })->values();

    $laundryData['advertisement'] = $laundry->advertisement->where('status', 'confirmed')
        ->where('end_date', '>', now())  ->values();  
     $laundryData['LaundryMedia'] = $laundry->LaundryMedia;
  
    return $this->sendResponse($laundryData,'laundry fetched successfully.');
} catch (\Exception $e) {
    // Handle any exceptions and return an error response
    return response()->json(['error' => $e->getMessage()], 500);
}
}


public function UpdateStatusLaundery(Request $request)
{
    
    try {

        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:laundries', 
        ]);
       
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());
        }
        // Fetch the laundry belonging to the authenticated user
        $laundry = Laundry::findOrFail($request->id);



// Toggle the isActive field
$laundry->isActive = !$laundry->isActive;  // If 1, it becomes 0, and vice versa
$laundry->save();

        return $this->sendResponse($laundry,'laundry updated successfully.');

    
    } catch (\Throwable $th) {
        return response()->json([
            'status' => false,
            'message' => $th->getMessage()
        ], 500); 
    
    } 
}




public function UpdateUrgent(Request $request)
{
    
    try {

        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:laundries', 
        ]);
       
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());
        }
        // Fetch the laundry belonging to the authenticated user
        $laundry = Laundry::findOrFail($request->id);
$laundry->urgent = !$laundry->urgent;  // If 1, it becomes 0, and vice versa
$laundry->save();

        return $this->sendResponse($laundry,'laundry updated successfully.');

    
    } catch (\Throwable $th) {
        return response()->json([
            'status' => false,
            'message' => $th->getMessage()
        ], 500); 
    
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
