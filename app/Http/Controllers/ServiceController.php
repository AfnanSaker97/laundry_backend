<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Support\Facades\DB;
use Validator;
use Auth;
use Illuminate\Support\Facades\Cache;
class ServiceController extends BaseController
{
    

    public function index()
    {
        try{
        $Services =  Service::all();

        return $this->sendResponse($Services, 'Services fetched successfully.');
    } catch (\Exception $e) {
     
        return response()->json(['error' =>  $e->getMessage()], 500);
      
    }
    }

    public function store(Request $request)
    {
        try {
        $validator =Validator::make($request->all(), [
            'name_ar' => 'required|string|unique:services,name_ar',
            'name_en' => 'required|string|unique:services,name_en',
          
        ]);
       
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors()->all());       
        }
      
       
           $service = Service::create([
            'name_ar' => $request->name_ar,
            'name_en' => $request->name_en,
         
        ]);

      
         Cache::forget('services');
        return $this->sendResponse($service,'service added successfully.');

    } catch (\Exception $e) {
     
        return response()->json(['error' =>  $e->getMessage()], 500);
      
    }    }


    
public function update(Request $request)
{
    try {
    
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:services,id',
            'name_en' => 'nullable|string|unique:services,name_en,' . $request->id,
            'name_ar' => 'nullable|string|unique:services,name_ar,' . $request->id,
       ]);
   
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());
        }

        $service = Service::findOrFail($request->id);   
        $service->update([
            'name_en' => $request->name_en,
            'name_ar' => $request->name_ar,
        ]);
        Cache::forget('services');
        return $this->sendResponse($service, 'service updated successfully.');

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}





public function delete(Request $request)
{
    try {
    
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:services,id',
        ]);
   
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());
        }

        $service = Service::findOrFail($request->id);
       
        $service->delete();
    
        Cache::forget('services');
        return $this->sendResponse($service, 'service deleted successfully.');

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

}
