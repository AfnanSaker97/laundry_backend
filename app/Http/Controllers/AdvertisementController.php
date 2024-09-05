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
