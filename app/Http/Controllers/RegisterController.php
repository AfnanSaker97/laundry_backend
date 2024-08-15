<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Models\MySession;
use Carbon\Carbon;
use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationCodeMail;
use Validator;
use Auth;
class RegisterController extends BaseController
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|min:3',
            'last_name' => 'required|min:3',
            'email' => 'required|email',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors()->all());       
        }
        try {
            $email = $request->email;
            $email_verification_code = random_int(1000, 9999); 
           
            // Create a new user
            $user = User::create([
                'first_name' =>  $request->first_name,
                'last_name' =>  $request->last_name,
                'email' =>  $email,
                'verification_code' => $email_verification_code ,
                'user_type_id' =>  2,
            ]);

            MySession::create([
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'payload' => base64_encode($request->getContent()), 
                'last_activity' => time(),
            ]);
            $success['user'] =  $user;
            Mail::to($user->email)->send(new VerificationCodeMail($email_verification_code)); 
        return $this->sendResponse($success,'Verification code sent to your email.');
  
    }catch (\Throwable $th) {
        return response()->json([
            'status' => false,
            'message' => $th->getMessage()
        ], 500);
    }
}


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



     public function getUser(Request $request)
{

    $user =Auth::user();
    return $this->sendResponse($user, 'User fetched successfully.');
 

}


public function logout(Request $request)
{
    $user = auth()->user();
    $user->tokens()->delete();
  //  $user->mySession()->delete();
    $success['user'] =  $user;
    return $this->sendResponse($success, 'User is logged out successfully.');

} 



public function update(Request $request)
{

    try {
    $user =Auth::user();
    if ($request->filled('first_name')) {
        $user->update(['first_name' => $request->first_name]);
    }
    if ($request->filled('last_name')) {
        $user->update(['last_name' => $request->last_name]);
    }
 
    if ($request->photo) {
    
        $imageName = time() . '.' . $request->photo->extension();
        $request->photo->move(public_path('driver'), $imageName);
        $url = url('driver/' . $imageName);
        $user->photo = $url;
        return $user;
        $user->save();
    }
 
    return $this->sendResponse($user, 'User updated successfully.');
 
} catch (\Exception $e) {
    // Log error and return empty array
    return response()->json(['error' =>  $e->getMessage()], 500);
  
}
}

}
