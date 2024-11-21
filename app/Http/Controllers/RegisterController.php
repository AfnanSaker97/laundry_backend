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
use Illuminate\Support\Facades\Cache;
use Validator;
use Auth;
class RegisterController extends BaseController
{

    // Create a session for the user
private function createUserSession($userId, $request)
{
    MySession::create([
        'user_id' => $userId,
        'ip_address' => $request->ip(),
        'user_agent' => $request->header('User-Agent'),
        'payload' => base64_encode($request->getContent()),
        'last_activity' => time(),
    ]);
}

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'email' => 'required|email',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors()->all());       
        }
        try {
            $email = $request->email;
            $email_verification_code = random_int(1000, 9999); 
            $existingUser = User::where('email', $request->email)->first();
            if(!$existingUser)
            { 
                $user = User::withTrashed()->where('email', $email)->first();
                if ($user) {
                    $user->restore();
                      // Update user details
               $user->update([
                'name' => $request->name,
                'verification_code' => $email_verification_code,
                'photo' => 'https://laundry-backend.tecrek.com/public/User/11.jpg',
                'points_wallet' => '0.0',
                'lat' => '0.0',
                'lng' => '0.0',
                'device_token' => '0.0',
              ]);
            } else {
                // إذا لم يكن هناك مستخدم محذوف، نقوم بإنشاء مستخدم جديد
                $user = User::create([
                    'name' =>  $request->name,
                    'email' =>  $email,
                    'verification_code' => $email_verification_code,
                    'user_type_id' =>  2,
                    'photo' =>  'https://laundry-backend.tecrek.com/public/User/11.jpg',
                ]);
            }
        $this->createUserSession($user->id, $request);
        Mail::to($user->email)->send(new VerificationCodeMail($email_verification_code));
     }
        
     else {
 
        $existingUser->verification_code = $email_verification_code;
        $existingUser->save();
        $this->createUserSession($existingUser->id, $request);
        Mail::to($existingUser->email)->send(new VerificationCodeMail($email_verification_code));
    }
    $response = [
        'success' => true,
        'message' => 'Verification code sent to your email.',
    ];

    return response()->json($response, 200);
 }catch (\Throwable $th) {
        return response()->json([
            'status' => false,
            'message' => $th->getMessage()
        ], 500);
    }
}






    
public function verify(Request $request)
{

    try {
    // Validate the request input
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:users,email',
        'verification_code' => 'required|string',
    ]);

    // Check if validation fails
  
    if($validator->fails()){
        return $this->sendError('Validation Error.', $validator->errors()->all());       
    }

    // Retrieve the user by email and check the verification code
    $user = User::where('email', $request->email)
                ->where('verification_code', $request->verification_code)
                ->first();

    // If user does not exist or code is incorrect, return an error response
    if (!$user) {
        return $this->sendError('Validation Error.',['Invalid verification code!']);
    }
      
        // Update user's email verification timestamp
        $user->email_verified_at = Carbon::now();
        $user->save();

        // Create a new API token for the user
        $token = $user->createToken($request->email)->plainTextToken;
        $device_token =$user->update(['device_token'=>$request->device_token]);
        // Filtered user data (excluding sensitive fields)
        $filteredUser = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'user_type_id' => $user->user_type_id,
        ];

        $data = [
            'token' => $token,
            'user' => $filteredUser,
            
        ];

        return $this->sendResponse($data,'Email verified successfully.');
    } catch (\Throwable $th) {
        return response()->json([
            'status' => 'error',
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
                'name' => $request->name,
               // 'last_name' =>'0',
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
         try {
            $user = Auth::user();
          // جلب فقط الحقول المطلوبة
        $userData = $user->only(['id', 'name', 'email', 'photo', 'points_wallet']);
           // جلب العنوان النشط للمستخدم عندما يكون isActive = 1
        $activeAddress = $user->addresses()->where('isActive', 1)->first();
        if ($activeAddress) {
            $userData['contact_number'] = $activeAddress->contact_number;
        } else {
            // إذا لم يكن هناك عنوان نشط، يتم إرجاع '0' كقيمة افتراضية
            $userData['contact_number'] = '0';
        }
             return $this->sendResponse($userData, 'User fetched successfully.');
             
         } catch (\Exception $e) {
             // Handle any exceptions and return an error response
             return response()->json(['error' => $e->getMessage()], 500);
         }
     }
     



public function logout(Request $request)
{
    $user = auth()->user();
    $user->tokens()->delete();
  //  $user->mySession()->delete();
    $response = [
        'success' => true,
        'message' => 'User is logged out successfully.',
    ];

    return response()->json($response, 200);


} 



public function update(Request $request)
{

    try {
    $user =Auth::user();
    if ($request->filled('name')) {
        $user->update(['name' => $request->name]);
    }
 
    if ($request->photo) {
    
        $imageName = time() . '.' . $request->photo->extension();
        $request->photo->move(public_path('User'), $imageName);
        $url = url('User/' . $imageName);
        $user->photo = $url;
        $user->save();
    }
 
    return $this->sendResponse($user, 'User updated successfully.');
 
} catch (\Exception $e) {
    // Log error and return empty array
    return response()->json(['error' =>  $e->getMessage()], 500);
  
}
}




public function destroy(Request $request)
{
  try {
        $user = Auth::user();
        $user->addresses()->delete();
        $user->orders()->delete();
        $user->advertisements()->delete();
        $user->delete();

        return $this->sendResponse($user,'user deleted successfully.');
    } catch (\Throwable $th) {
        return response()->json([
            'status' => false,
            'message' => $th->getMessage()
        ], 500); 
    
    } 
}
}
