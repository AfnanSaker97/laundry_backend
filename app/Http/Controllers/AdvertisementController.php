<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Advertisement;
use Carbon\Carbon;
use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Validator;
use Auth;
class AdvertisementController extends BaseController
{
    

    
    public function getAdvertisement(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'laundry_id' => 'nullable|exists:laundries,id',
            'name' => 'nullable|string|max:255',
            'status_id' => 'nullable|in:1,0',
            ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 422);
    }
        try {
        $user = Auth::user();
        $query = Advertisement::query();
        if ($request->filled('status_id')) {
            $query->where('isActive', $request->status_id);
        }

        if ($request->filled('laundry_id')) {
            $query->where('laundry_id', $request->laundry_id);
        }

        if ($request->filled('name')) {
            $query->where(function ($q) use ($request) {
                $q->where('name_en', 'like', '%' . $request->name . '%')
                  ->orWhere('name_ar', 'like', '%' . $request->name . '%');
            });
        }
        if ($user->user_type_id == 4) {
            $advertisements = $query->with('laundry:id,name_en,description_en','Media')->paginate(5);
        } elseif ($user->user_type_id == 1) {
            $advertisements = $query->where('laundry_id', $user->laundry->id)
                                     ->with('laundry:id,name_en,description_en','Media')
                                     ->paginate(5);
        } else {
            return $this->sendError('Access Denied. User type not authorized to access advertisements.', [], 403);
        }

        
        return $this->sendResponse($advertisements,'Advertisement fetched successfully.');
  
    } catch (\Throwable $th) {
        return response()->json([
            'status' => 'error',
            'message' => $th->getMessage()
        ], 500);
    }
  }




    public function store(Request $request)
    {
        try{

        $validator = Validator::make($request->all(), [
            'laundry_id' => 'nullable|exists:laundries,id',
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'url_media' => 'required|array', // Ensure url_media is an array
            'url_media.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Validate each image
            'points' => 'nullable|numeric|min:1|max:99999999.9',
            'NumberDays' => 'required|numeric|min:1', ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 422);
    }
    $numberDays = (int) $request->NumberDays;
    $endDate = now()->addDays($numberDays);

    $advertisement = Advertisement::create([
        'laundry_id' => $request->laundry_id,
        'name_ar' => $request->name_ar,
        'name_en' => $request->name_en,
        'points' => $request->points ?? 0,
        'end_date' => $endDate,
    ]);
    foreach ($request->file('url_media') as $image) {
        $imageName = time() . '_' . uniqid() . '.' . $image->extension(); // Create a unique file name
        $image->move(public_path('Advertisement'), $imageName); // Move the file to the specified directory
        $url = url('Advertisement/' . $imageName); // Create the URL for the uploaded image

        // Store media related to the advertisement
        $advertisement->media()->create([
            'url_image' => $url,
            'advertisement_id' => $advertisement->id,
        ]);
    }

        return $this->sendResponse($advertisement,'Advertisement added successfully but requires confirmation from the super admin.');
    } catch (\Throwable $th) {
        return response()->json([
            'status' => false,
            'message' => $th->getMessage()
        ], 500); 
    
    } 
    }

    public function index(Request $request)
    {
        try {
            // Validate input data
            $validator = Validator::make($request->all(), [
                'laundry_id' => 'required|exists:laundries,id',
            ]);
    
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }
    
            // Set the current date
            $endDate = now()->format('Y-m-d');

            // Fetch advertisements with conditions
            $advertisements = Advertisement::where('isActive', 1)
                ->where('end_date', '>', $endDate)
                 ->where('laundry_id', $request->laundry_id)->with('Media')
                ->get();
    
            // Return a successful response with fetched advertisements
            return $this->sendResponse($advertisements, 'Advertisements fetched successfully.');
    
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching advertisements: ' . $th->getMessage()
            ], 500);
        }
    }
    


    public function confirmAdvertisement(Request $request)
    {
        try {
            // Validate input data
            $validator = Validator::make($request->all(), [
                'advertisement_id' => 'required|exists:advertisements,id',
            ]);
    
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }
    
            // Fetch advertisements with conditions
            $advertisement = Advertisement::findOrFail($request->advertisement_id);
            $advertisement->update(['isActive' => 1]);
            // Return a successful response with fetched advertisements
            return $this->sendResponse($advertisement, 'Advertisements fetched successfully.');
    
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching advertisements: ' . $th->getMessage()
            ], 500);
        }
    }
    


    public function show(Request $request)
    {
    
        try {
            // Validate input data
            $validator = Validator::make($request->all(), [
                'advertisement_id' => 'required|exists:advertisements,id',
            ]);
    
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }
            // Fetch advertisements with conditions
            $advertisement = Advertisement::with('Media')->findOrFail($request->advertisement_id);
            // Return a successful response with fetched advertisements
            return $this->sendResponse($advertisement, 'Advertisements fetched successfully.');
    
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching advertisements: ' . $th->getMessage()
            ], 500);
        }
    }


    public function update(Request $request)
    {
        try {
            // Validate the incoming request
            $validator = Validator::make($request->all(), [
                'advertisement_id' => 'required|exists:advertisements,id',
                'name_ar' => 'nullable|string|max:255',
                'name_en' => 'nullable|string|max:255',
                'points' => 'nullable|numeric|min:1|max:99999999.9',
                'NumberDays' => 'nullable|numeric|min:1',
            ]);
    
            // Handle validation failures
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }
    
            // Find the advertisement
            $advertisement = Advertisement::findOrFail($request->advertisement_id);
    
            // Prepare update data
            $updateData = [
                'name_ar' => $request->name_ar ?? $advertisement->name_ar,
                'name_en' => $request->name_en ?? $advertisement->name_en,
                'points' => $request->points ?? $advertisement->points,
            ];
    
            // Update end_date if NumberDays is provided
            if ($request->has('NumberDays')) {
                $numberDays = (int)$request->NumberDays;
                $updateData['end_date'] = now()->addDays($numberDays);
            }
    
            // Update the advertisement
            $advertisement->update($updateData);
    
            // Return a success response
            return $this->sendResponse($advertisement, 'Advertisement updated successfully.');
        } catch (\Throwable $th) {
            // Handle any unexpected errors
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
    



  public function clickAdvertisement(Request $request)
{
    
    try{
    $validator =Validator::make($request->all(), [
       
        'advertisement_id'=>'required|exists:advertisements,id',
    ]); 

    if ($validator->fails()) {
        return $this->sendError('Validation Error.', $validator->errors()->all());       
    }
    // احصل على المستخدم الحالي
    $user = Auth::user();
 
    // ابحث عن الإعلان
    $advertisement = Advertisement::findOrFail($request->advertisement_id);
    
    // تحقق إذا كان المستخدم قد ضغط على الإعلان من قبل
    $existingClick = $user->advertisements()->where('advertisement_id', $request->advertisement_id)->exists();
    
    if (!$existingClick) {
       // إضافة نقاط الإعلان إلى محفظة المستخدم
    $user->points_wallet += $advertisement->points;
    $user->save();
        // إذا لم يضغط المستخدم من قبل، أكسبه نقاط الإعلان
        $user->advertisements()->attach($request->advertisement_id, ['points' => $advertisement->points]);
        return $this->sendResponse($advertisement,'You have earned points for this advertisement');
  
    }
    return $this->sendResponse([],'You have already clicked on this ad and earned points.');
} catch (\Throwable $th) {
    return response()->json([
        'status' => 'error',
        'message' => $th->getMessage()
    ], 500);
}
}

}
