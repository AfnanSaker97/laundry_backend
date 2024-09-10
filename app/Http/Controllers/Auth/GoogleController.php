<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Socialite;
use App\Models\User;
use Auth;
use App\Http\Controllers\BaseController as BaseController;

class GoogleController extends BaseController
{
    public function handleGoogleCallback(Request $request)
    {
        try {
            $token = $request->input('token');
          
            $user = Socialite::driver('google')->stateless()->userFromToken($token);

            // ابحث عن المستخدم بناءً على بريد Google
            $existingUser = User::where('email', $user->getEmail())->first();
            
            if ($existingUser) {
                // تسجيل الدخول تلقائيًا
                Auth::login($existingUser, true);
            } else {
                // إنشاء مستخدم جديد
                $newUser = User::create([
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'google_id' => $user->getId(),
              ]);

                Auth::login($newUser, true);
            }

            // إنشاء توكن API
            $token = $newUser->createToken($newUser->email)->plainTextToken;
            $user =[
                'token' => $token,
                'user' => $newUser,
            ];
            return $this->sendResponse($user, 'User login successfully.');
           
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Redirect to Google
    /*  public function redirectToGoogle()
      {
          return Socialite::driver('google')->stateless()->redirect();
      }

         // Handle Google callback
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Check if the user exists in the database
            $user = User::where('email', $googleUser->email)->first();

            if (!$user) {
                // If the user doesn't exist, create a new user
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                 //   'google_id' => $googleUser->id,
                 ]);
            }

            // Log in the user
            Auth::login($user, true);

            // Generate an API token (optional)
            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to authenticate.'], 401);
        }
    }*/

}
