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
    

    
    public function getAdvertisement()
    {
        try {
        $user = Auth::user();
        if ($user->user_type_id == 4) {
        $Advertisement =  Advertisement::all();
    } elseif ($user->user_type_id == 1) { 
    
        $Advertisement =  Advertisement::where('laundry_id',$user->laundry->id)->get();
        }
        
        return $this->sendResponse($Advertisement,'Advertisement fetched successfully.');
  
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
            'url_media' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'points' => 'nullable|numeric|min:1|max:99999999.9',
            'NumberDays' => 'required|numeric|min:1', ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 422);
    }
    $numberDays = (int) $request->NumberDays;
    $endDate = now()->addDays($numberDays);
    $image = $request->file('url_media'); // Get the file from the request
    $imageName = time() . '.' . $image->extension(); // Create a unique file name
    $image->move(public_path('Advertisement'), $imageName); // Move the file to the specified directory
    $url = url('Advertisement/' . $imageName); // Create the URL for the uploaded image


    $advertisement = Advertisement::create([
        'laundry_id' => $request->laundry_id,
        'name_ar' => $request->name_ar,
        'name_en' => $request->name_en,
        'url_media' => $url,
        'points' => $request->points ?? 0,
        'end_date' => $endDate,
    ]);

        return $this->sendResponse($advertisement,'Advertisement added successfully but requires confirmation from the super admin.');
    } catch (\Throwable $th) {
        return response()->json([
            'status' => false,
            'message' => $th->getMessage()
        ], 500); 
    
    } 
    }


    public function index()
    {
        try {
        $Advertisement = Cache::remember('advertisement', 60, function () {
            return Advertisement::all();
        });
        return $this->sendResponse($Advertisement,'Advertisement fetched successfully.');
  
    } catch (\Throwable $th) {
        return response()->json([
            'status' => 'error',
            'message' => $th->getMessage()
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
