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
                'array_ids.*.order_type_id' => 'required|exists:order_types,id',
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
                    'order_type_id' => $item['order_type_id'],
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
           'page' => 'nullable' 
        ]);
    
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());
        }
        try{
        $user = Auth::user();
        $Laundries = [];

        if ($user->user_type_id == 1) {
            $query = Laundry::with(['LaundryMedia', 'price.laundryItem','price.service', 'price.OrderType','addresses'])
                ->where('admin_id', $user->id);
        } elseif ($user->user_type_id == 4) {
            $query = Laundry::with(['LaundryMedia', 'price.laundryItem','price.service','price.OrderType','addresses']);
        }

       if ($request->has('page') && $request->page == 0) {

            $Laundries = $query->get();
            $paginationData = null; 
        } else {
            $Laundries = $query->paginate(10);
            $paginationData = [
                'current_page' => $Laundries->currentPage(),
                'last_page' => $Laundries->lastPage(),
                'total' => $Laundries->total(),
                'from' => $Laundries->firstItem(),
                  'to' => $Laundries->lastItem(),
              'per_page' => $Laundries->perPage(),
            ];
        }
        $formattedLaundries = $Laundries->map(function ($laundry) {
            return [
                'laundry' => [
                    'id' => $laundry->id,
                    'name_en' => $laundry->name_en,
                    'name_ar' => $laundry->name_ar,
                    'description_en' => $laundry->description_en,
                    'description_ar' => $laundry->description_ar,
                    'email' => $laundry->email,
                    'phone_number' => $laundry->phone_number,
                    'isActive' => $laundry->isActive,
                    'urgent' => $laundry->urgent,
                    'addresses' => $laundry->addresses,
                   
                    'details' => $laundry->price->groupBy('laundryItem.item_type_en')->map(function ($items, $itemName) {
                        return [
                            'item' => [
                                'id' => $items->first()->laundryItem->id,
                                'en' => $itemName,
                                'ar' => $items->first()->laundryItem->item_type_ar ,
                              'url_image' => $items->first()->laundryItem->url_image 
                            ],
                            'services' => $items->groupBy('service.name_en')->map(function ($services, $serviceName) {
                                return [
                                    'service' => [
                                        'id' => $services->first()->service->id,
                                        'en' => $serviceName,
                                        'ar' => $services->first()->service->name_ar ,
                                    ],
                                    'prices' => $services->sortBy('order_type_id')
                                        ->map(function ($service) {
                                            return $service->price;
                                        })->values()
                                ];
                            })->values(),
                        ];
                    })->values(),
                    'media' => $laundry->LaundryMedia,
                ]
            ];
        });
        return $this->sendResponse(
            [
                'laundries' => $formattedLaundries,
                'pagination' => $paginationData
            ],
            'Laundries fetched successfully.'
        );

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
                'array_ids.*.laundry_item_id' => 'required|exists:laundry_items,id',
                'array_ids.*.order_type_id' => 'required|exists:order_types,id',
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
         
            Price::where('laundry_id', $laundry->id)->delete(); // Remove old prices
            $pricesData = array_map(function ($item) use ($laundry) {
                return [
                    'laundry_id' => $laundry->id,
                    'laundry_item_id' => $item['laundry_item_id'],
                    'service_id' => $item['service_id'],
                    'order_type_id' => $item['order_type_id'],
                    'price' => $item['price'],
                ];
            }, $request->array_ids);
            Price::insert($pricesData);
            if ($laundry->addresses) {
                $laundry->addresses()->update([
                    'city' => $request->city,
                    'address_line_1' => $request->address_line_1,
                    'lat' => $request->lat,
                    'lng' => $request->lng,
                ]);
            } else {
                // If no address exists, you can either create a new address or handle the error.
                $laundry->addresses()->create([
                    'city' => $request->city,
                    'address_line_1' => $request->address_line_1,
                    'lat' => $request->lat,
                    'lng' => $request->lng,
                ]);
            }
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
        $Laundries =  Laundry::with(['LaundryMedia'])
                           ->where('isActive', 1)
                            ->inRandomOrder()
                            ->get()
                            ->makeHidden(['isActive','created_at','updated_at','email']);
    
    
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
    
            $results = Laundry::with('LaundryMedia')
            ->select('id', 'name_en', 'name_ar', 'description_en', 'description_ar') 
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
        try {
            // التحقق من صحة الإدخال
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:laundries,id',
            ]);
    
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors()->all());
            }

            $laundry = Laundry::with([
                'LaundryMedia',
                'LaundryItem',
                'services',
                'addresses',
                'advertisement.Media',
                'price.laundryItem',
                'price.OrderType',
                'price.service'
            ])->findOrFail($request->id);
   
            // تجميع البيانات بطريقة منظمة
            $laundryData = [
                'id' => $laundry->id,
                'name_en' => $laundry->name_en,
                'name_ar' => $laundry->name_ar,
                'description_en' => $laundry->description_en,
                'description_ar' => $laundry->description_ar,
                'email' => $laundry->email,
                'phone_number' => $laundry->phone_number,
                'urgent' => $laundry->urgent,
                'addresses' => $laundry->addresses,
                'details' => $laundry->price
                ->groupBy('laundryItem.item_type_en') 
                ->map(function ($items, $itemName) {
                    return [
                        'item' => [
                            'id' => $items->first()->laundryItem->id,
                            'en' => $itemName, 
                            'ar' => $items->first()->laundryItem->item_type_ar ,
                            'url_image' => $items->first()->laundryItem->url_image 
                        ],
                        'services' => $items->groupBy('service.name_en')->map(function ($services, $serviceName) {
                            return [
                                'service' => [
                                    'id' =>$services->first()->service->id,
                                    'en' => $serviceName, 
                                    'ar' => $services->first()->service->name_ar 
                                ],
                                'prices' => $services->sortBy('order_type_id') 
                                ->map(function ($service)  {
                                  
                                    return [$service->price
                                    ];
                                })->values()
                            ];
                        })->values()
                    ];
                })->values(),
            'ads' => $laundry->advertisement
                ->where('status', 'confirmed')
                ->where('end_date', '>', now())
                ->values(),
        


                'ads' => $laundry->advertisement
                    ->where('status', 'confirmed')
                    ->where('end_date', '>', now())
                    ->values(),
                'media' => $laundry->LaundryMedia,
            ];
    
            return $this->sendResponse($laundryData, 'Laundry fetched successfully.');
        } catch (\Exception $e) {
            // معالجة الأخطاء وإرجاع استجابة
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
        // Get the authenticated user's location
        $user = Auth::user();
        $userLatitude = $user->lat;
        $userLongitude = $user->lng;

        // Fetch laundries ordered by proximity using address coordinates
        $laundries = Laundry::select(
                'laundries.id',
                'laundries.name_ar',
                'laundries.name_en',
                'laundries.description_ar',
                'laundries.description_en',
                'laundries.phone_number',
                DB::raw("(6371 * acos(cos(radians(?)) * cos(radians(address_laundries.lat)) * cos(radians(address_laundries.lng) - radians(?)) + sin(radians(?)) * sin(radians(address_laundries.lat)))) AS distance")
            )
            ->addBinding([$userLatitude, $userLongitude, $userLatitude], 'select') // Parameter binding
            ->join('address_laundries', 'laundries.id', '=', 'address_laundries.laundry_id') // Join with addresses table
            ->where('laundries.isActive', 1)
           ->orderBy('distance')
            ->with([
                'addresses', 
                'LaundryMedia', // Select specific fields in LaundryMedia
            ])
            ->get();

        return $this->sendResponse($laundries, 'Laundries fetched successfully.');
        
    } catch (\Exception $e) {
        // Handle any exceptions and return an error response
        return response()->json(['error' => $e->getMessage()], 500);
    }

}
        
}
