<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Support\Facades\DB;
use Validator;
use Auth;
use Illuminate\Database\QueryException;
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
            'url_image' => 'required|file',
        ]);
       
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors()->all());       
        }
      
        $image =$request->file('url_image');
           $imageName = time() . '_' . uniqid() . '.' . $image->extension();
           $image->move(public_path('Service'), $imageName);
           $url = url('Service/' . $imageName);

           $service = Service::create([
            'name_ar' => $request->name_ar,
            'name_en' => $request->name_en,
            'url_image' => $url,
         
        ]);

      
         Cache::forget('services');
        return $this->sendResponse($service,'service added successfully.');

    } catch (\Exception $e) {
     
        return response()->json(['error' =>  $e->getMessage()], 500);
      
    }    }





    public function show(Request $request)
{
    $validator = Validator::make($request->all(), [
        'id' => 'required|exists:services,id',  
    ]);

    if ($validator->fails()) {
        return $this->sendError('Validation Error.', $validator->errors()->all());
    }

    try {
     
        $service = Service::findOrFail($request->id);


        return $this->sendResponse($service, 'service fetched successfully.');
    } catch (\Exception $e) {
      
        return response()->json(['error' => $e->getMessage()], 500);
    }
}



    
public function update(Request $request)
{
    try {
    
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:services,id',
            'name_en' => 'nullable|string|unique:services,name_en,' . $request->id,
            'name_ar' => 'nullable|string|unique:services,name_ar,' . $request->id,
            'url_image' => 'nullable|file',
       ]);
   
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());
        }

        $service = Service::findOrFail($request->id); 
        $url = $service->url_image; 

        if ($request->hasFile('url_image')) {
            $image = $request->file('url_image');
            $imageName = time() . '_' . uniqid() . '.' . $image->extension();
            $image->move(public_path('Service'), $imageName);
            $url = url('Service/' . $imageName);

           
            if ($service->url_image && file_exists(public_path(parse_url($service->url_image, PHP_URL_PATH)))) {
                unlink(public_path(parse_url($service->url_image, PHP_URL_PATH)));
            }
        }

        $service->update([
            'name_en' => $request->name_en,
            'name_ar' => $request->name_ar,
            'url_image' => $url,
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
    }
        catch (QueryException $e) {
            if ($e->getCode() == 23000) {
             
                return $this->sendError('error', 'Cannot delete the service because it has related items in the prices table.');
            }
        
        return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
