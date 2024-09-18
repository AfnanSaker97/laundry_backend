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
                    'name' => $request->name,
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



           




}
