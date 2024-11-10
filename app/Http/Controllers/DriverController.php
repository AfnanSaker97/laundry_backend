<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Driver;
use App\Models\MySession;
use Carbon\Carbon;
use App\Http\Controllers\BaseController as BaseController;
use Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
class DriverController extends BaseController
{
    
    public function createDriver(Request $request)
    {
    
        try {
        $validator = Validator::make($request->all(), [
           'name'=>'required|min:3',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'laundry_id' => 'required|exists:laundries,id',
      
        ]);
 
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());
        }
      
        $user = User::create([
           'name' => $request->name,
           'email' => $request->email,
           'password' => Hash::make($request->password),
           'user_type_id'=> 3, 
           'driver_id' => $this->generateDriverId(),
           'photo' => 'https://laundry-backend.tecrek.com/public/User/11.jpg',
                
       ]);  
     
            $driver = Driver::create([
            'user_id' => $user->id, 
            'laundry_id' => $request->laundry_id, 
        ]);
                $data['token'] = $user->createToken($request->email)->plainTextToken;
                $data['user'] = $user;
                return $this->sendResponse($data,'Driver is created successfully');
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['error' => $e->getMessage()], 500);
            }
     } 

     
     public function getDriversByLaundryId(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
             'laundry_id' => 'nullable|exists:laundries,id',
            
         ]);
  
         if ($validator->fails()) {
             return $this->sendError('Validation Error.', $validator->errors()->all());
         }

        $user = $request->user();
        $query = Driver::with('user');
        if ($user->user_type_id == 1) {
            $query->where('laundry_id', $user->laundry->id);

            if ($request->has('name')) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->name . '%');
                });
            }
        }

        // إذا كان نوع المستخدم هو 4، عرض جميع السائقين
        elseif ($user->user_type_id == 4) {
            if ($request->has('name')) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->name . '%');
                });
            }
            if ($request->filled('laundry_id')) {
                $query->where('laundry_id', $request->laundry_id);
            }
        }

        if ($request->has('page') && $request->page == 0) {
            $drivers = $query->get(); 
        } else {
            $drivers = $query->paginate(10);
        }

        return $this->sendResponse($drivers, 'Drivers fetched successfully.');
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


     public function show(Request $request)
     {
       try {
         $validator =Validator::make($request->all(), [
             'id' => 'required|exists:users',
         ]); 
     
         if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());
        }
           $user =User::findOrFail($request->id);
             return $this->sendResponse($user,'Driver updated successfully.');
         } catch (\Throwable $th) {
             return response()->json([
                 'status' => false,
                 'message' => $th->getMessage()
             ], 500); 
         
         } 
     }
          
    

     

    public function update(Request $request)
    {
      try {
        $validator =Validator::make($request->all(), [
            'id' => 'required|exists:users',
        ]); 
    
        if ($request->photo) {
            $imageName = time() . '.' . $request->photo->extension();
            $request->photo->move(public_path('User'), $imageName);
            $url = url('User/' . $imageName);
        }
     
          $user =User::findOrFail($request->id);
          $user->update([
            'name' => $request->name ?? $user->name,
            'photo' => $url ?? $user->photo,
            'password' =>Hash::make($request->password) ?? $user->password,
        ]);
  
            return $this->sendResponse($user,'Driver updated successfully.');
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500); 
        
        } 
    }

public function destroy(Request $request)
{
  try {
    $validator =Validator::make($request->all(), [
        'id' => 'required|exists:users',
    ]); 

      $user =User::findOrFail($request->id);
      $user->delete();

        return $this->sendResponse($user,'Driver deleted successfully.');
    } catch (\Throwable $th) {
        return response()->json([
            'status' => false,
            'message' => $th->getMessage()
        ], 500); 
    
    } 
}

     
 private function generateDriverId()
 {
     // Example implementation - you can customize this as needed
     return 'DRV-' . strtoupper(uniqid());
 }
}
