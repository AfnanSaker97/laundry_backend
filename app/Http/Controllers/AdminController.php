<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\MySession;
use Carbon\Carbon;
use App\Http\Controllers\BaseController as BaseController;
use Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
class AdminController extends BaseController
{

       
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status_id' => 'required|in:2,3',  // Corrected the validation rule
        ]);
    
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());
        }
    
        $userTypeId = $request->status_id;
        $users = User::where('user_type_id', $userTypeId)->get();
        
        $message = $userTypeId == 2 ? 'Users fetched successfully.' : 'Drivers fetched successfully.';
        
        return $this->sendResponse($users, $message);
    }


   
    public function registerAdmin(Request $request)
    {
        try {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255|unique:users,email',
            'name'=>'required|min:3',
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
 
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());
        }
      
                $user = User::create([
                    'first_name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'user_type_id'=> 1, 
                ]);  
              
                $data['token'] = $user->createToken($request->email)->plainTextToken;
                $data['user'] = $user;
                return $this->sendResponse($data,'admin is created successfully');
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['error' => $e->getMessage()], 500);
            }
     }  



     public function loginAdmin(Request $request)
     {
        try {
         $validator = Validator::make($request->all(), [
             'email' => 'required|email|max:255',
             'password' => ['required', 'string'],
         ]);
     
         if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());
        }
    
                 // Check email exist
             $user = User::where('email', $request->email)->first();
         
             if(!$user) {
                return $this->sendError('Invalid email', []);
               
             }
         // Check the password
         if (!Hash::check($request->password, $user->password)) {
            return $this->sendError('Invalid password', []);
        }
       
             $data['token'] = $user->createToken($request->email)->plainTextToken;
             $data['user'] = $user;
             return $this->sendResponse($data,'User is logged in successfully.');
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['error' => $e->getMessage()], 500);
            }
             }



     



             public function createDriver(Request $request)
             {
             
                 try {
                 $validator = Validator::make($request->all(), [
                    'first_name'=>'required|min:3',
                    'last_name'=>'required|min:3',
                     'email' => 'required|email|max:255|unique:users,email',
                     'password' => ['required', 'string', 'min:8', 'confirmed'],
                 ]);
          
                 if ($validator->fails()) {
                     return $this->sendError('Validation Error.', $validator->errors()->all());
                 }
               
                 $user = User::create([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'user_type_id'=> 3, 
                    'driver_id' => $this->generateDriverId(),
                ]);  
              
                         $data['token'] = $user->createToken($request->email)->plainTextToken;
                         $data['user'] = $user;
                         return $this->sendResponse($data,'Driver is created successfully');
                     } catch (\Exception $e) {
                         DB::rollBack();
                         return response()->json(['error' => $e->getMessage()], 500);
                     }
              }  

 private function generateDriverId()
{
    // Example implementation - you can customize this as needed
    return 'DRV-' . strtoupper(uniqid());
}
         
}
