<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Cache;
use App\Models\Laundry;
use App\Models\LaundryItem;
use App\Models\LaundryMedia;
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
                'city' => 'required',
                'address_line_1'=> 'required',
                //'address' => 'required',
                'lat' => 'required',
                'lng' => 'required',
                'point' => 'required',
                'admin_id'=>'required|exists:users,id',
                "array_url.*.url_image" => 'required|file|mimes:jpg,png,jpeg,gif,svg,HEIF,BMP,webp|max:1500',
                'array_ids.*.laundry_item_id' => 'required|exists:laundry_items,id',
                'array_ids.*.price' => 'required|numeric',
                'array_service' => 'required|array',
                'array_service.*.service_id' => 'required|exists:services,id',
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
               // 'address' => $request->address,
                'point' => $request->point,
                'admin_id'=> $request->admin_id,
                'lat' => $request->lat,
                'lng' => $request->lng,

           ]);

           foreach ($request->file('array_url.*.url_image') as $index => $image) {
            // $folder = 'Picture';
             $imageName = time() . '.' . $image->extension();
             $image->move(public_path('Laundry'), $imageName);
             $url = url('Laundry/' . $imageName);
             $LaundryImage =  LaundryMedia::create(['laundry_id' => $laundry->id,
             'url_image' => $url,
              ]);
                 }

  // Store Laundry Items with prices
  foreach ($request->array_ids as $item) {
    Price::create([
        'laundry_id' => $laundry->id,
        'laundry_item_id' => $item['laundry_item_id'],
        'price' => $item['price'],
    ]);
}

foreach ($request->array_service as $service) {
    $laundry->services()->attach($service['service_id']);
}
          
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




    
public function update(Request $request)
{
    
    try {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:laundries', // Ensure the address exists
            'array_url.*.url_image' => 'nullable|file|mimes:jpg,png,jpeg,gif,svg,HEIF,BMP,webp|max:1500',
            'array_ids.*.laundry_item_id' => 'nullable|exists:laundry_items,id',
            'array_ids.*.price' => 'nullable|numeric',
            'array_service' => 'nullable|array',
            'array_service.*.service_id' => 'nullable|exists:services,id',
            'array_url.*.image_ids' => 'nullable|exists:laundry_media,id', // الصورة التي سيتم تعديلها
       
        ]);

        // Return validation errors if any
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());
        }

        // Find the address by ID
        $laundry = Laundry::findOrFail($request->id);
 
    // Update the address with the request data
    $laundry->update([
        'name_en' => $request->name_en ?? $laundry->name_en,
        'name_ar' => $request->name_ar ?? $laundry->name_ar,
        'description_ar' => $request->description_ar ?? $laundry->description_ar,
        'description_en' => $request->description_en ?? $laundry->description_en,
        'phone_number' => $request->phone_number ?? $laundry->phone_number,
        'city' => $request->city ?? $laundry->city,
        'address_line_1' => $request->address_line_1 ?? $laundry->address_line_1,
        'point' => $request->point ?? $laundry->point,
    ]);

        $imageFiles = $request->file('array_url.*.url_image');
        $imageIds = $request->input('array_url.*.image_ids'); // Retrieve the image IDs
        if ($imageFiles && $imageIds) {
        // Loop through each image file and its corresponding image ID
        foreach ($imageFiles as $index => $newImageFile) {
            // Find the corresponding image record by ID
            $image = LaundryMedia::findOrFail($imageIds[$index]);
    
            // Delete old image from storage
            $oldImagePath = public_path(parse_url($image->url_image, PHP_URL_PATH));
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }

            // Upload new image
            $imageName = time() . $index . '.' . $newImageFile->extension();
            $newImageFile->move(public_path('Laundry'), $imageName);
            $url = url('Laundry/' . $imageName);

            // Update image record in the database
            $image->update([
                'url_image' => $url,
            ]);
        }
    }
    $itemIds = $request->input('array_ids.*.laundry_item_id'); 
    $prices = $request->input('array_ids.*.price'); 
      
    if ($prices && $itemIds) {
        foreach ($prices as $index => $newPrice) {
            // Find the corresponding price record by laundry item and laundry ID
            $price = Price::where('laundry_item_id', $itemIds[$index])
                ->where('laundry_id', $laundry->id)
                ->firstOrFail();

            // Update the price
            $price->update([
                'price' => $newPrice,
            ]);
        }

}
 // Handle service updates
 $serviceIds = $request->input('array_service.*.service_id');

 if ($serviceIds) {
     // Detach any existing services
     $laundry->services()->sync($serviceIds); // Sync with the new set of service IDs
 }

        return $this->sendResponse($laundry, 'Laundry updated successfully.');

    } catch (\Throwable $th) {
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
    $user = Auth::user();
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

       // إذا كان المستخدم Admin، أضف poin إلى البيانات
       if ($user->user_type_id === 4) { // أو أي حقل آخر يعبر عن دور المستخدم كـ Admin
        $laundryData['point'] = $laundry->point; // تأكد من أن الحقل poin موجود في جدول المغاسل
    }

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
