<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Models\MySession;
use Carbon\Carbon;
use App\Http\Controllers\BaseController as BaseController;
use Validator;
class RegisterController extends BaseController
{
    

    public function registerPassword(Request $request)
    {
        try {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'name'=>'required|min:3',
            //'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors()->all());       
        }
        $existingUser = User::where('email', $request->email)->first();
       
         if($existingUser)
        {
            $data['token'] = $existingUser->createToken($request->email)->plainTextToken;
            $data['user'] = $existingUser;  
            $session = MySession::create([
                'user_id'=>$existingUser->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'payload'=> base64_encode($request->getContent()), 
                'last_activity' => time(),
            ]);
        }
        else{
            $user = User::create([
                'first_name' => $request->name,
                'last_name' =>'0',
                'email' => $request->email,
                'password' => Hash::make('12345678'),
                'user_type_id'=> 2, 
            ]);
            $session = MySession::create([
                'user_id'=>$user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'payload'=> base64_encode($request->getContent()), 
                'last_activity' => time(),
            ]);
            $data['token'] = $user->createToken($request->email)->plainTextToken;
            $data['user'] = $user;
        }
        return $this->sendResponse($data,'user is created successfully.');
    } catch (\Exception $e) {
        // Log error and return empty array
        return response()->json(['error' =>  $e->getMessage()], 500);
      
    }
      
               
     }  
}