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
            // Create a new user
            $user = User::create([
                'name' =>  $request->name,
                'email' =>  $email,
                'verification_code' => $email_verification_code ,
                'user_type_id' =>  2,
                 'photo' =>  'https://laundry-backend.tecrek.com/public/User/11.jpg',
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
  
    }
    else{
        $existingUser->verification_code =$email_verification_code;
        $existingUser->save();
        MySession::create([
            'user_id' => $existingUser->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'payload' => base64_encode($request->getContent()), 
            'last_activity' => time(),
        ]);
      //  $success['user'] =  $existingUser;
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
 /*   $userId = $request->input('email'); 
    $cacheKey = 'api-request-time-' . $userId;

    // تحقق مما إذا كان هناك وقت مخزن في الكاش
    $lastRequestTime = Cache::get($cacheKey);

    // إذا كان الطلب الأخير منذ أقل من 30 ثانية، أرجع رسالة خطأ
    if ($lastRequestTime && now()->diffInSeconds($lastRequestTime) < 30) {
        return response()->json(['error' => 'Please wait 30 seconds before sending another request.'], 429);
    }

    // قم بتخزين الوقت الحالي كوقت آخر طلب
    Cache::put($cacheKey, now(), 30); // تخزين لـ 30 ثانية
*/
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
        
            $user = User::select('id', 'name', 'email','photo') // Adjust fields as needed
            ->find(Auth::id());

          // Load the addresses and orders relationships
           $user->load('addresses', 'orders');
           $totalPoints = $user->orders->sum('point');
        
             // Hide the unwanted fields from the addresses and orders
        $user->addresses->makeHidden(['deleted_at', 'created_at', 'updated_at']);
        $user->orders->makeHidden([ 'created_at', 'updated_at']);

             // Return the user with the addresses relationship loaded
             $userData = $user;
             $userData['total_points'] = $totalPoints;
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
