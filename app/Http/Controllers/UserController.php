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
use Auth;
class UserController extends BaseController
{

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status_id' => 'required|in:1,2,3',  // Corrected the validation rule
        ]);
    
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());
        }
    
        $userTypeId = $request->status_id;
         
    // استرجاع المستخدمين بناءً على نوع المستخدم
    $query = User::where('user_type_id', $userTypeId);
    $users = $query->paginate(10);
        return $this->sendResponse($users, 'Users fetched successfully.');
    }


    public function search(Request $request)
    {
        $user = Auth::user(); 
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string',
            'status_id' => 'required|in:1,2', 
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());
        }
        if ($request->status_id == 1 && $user->user_type_id != 4) {
            return $this->sendError('Access Denied. Only Super Admin can use status_id = 1.');
        }
        $userTypeId = $request->status_id;
        // Build the query based on the request input
        $query = User::query();
        if ($user->user_type_id == 4) {
        $query->where('user_type_id', $userTypeId);  
        
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
    } elseif ($user->user_type_id == 1) { 
        // انضمام إلى جدول الأوامر للتحقق من ارتباط الزبائن بالمغسلة
        $query->where('user_type_id', $userTypeId)
              ->whereHas('orders', function ($q) use ($user) {
                  $q->where('laundry_id', $user->laundry->laundry_id);
              });

        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
    }
       
        // Fetch the users with pagination (optional)
        $users = $query->paginate(10);
        return $this->sendResponse($users, 'Users fetched successfully.');
   
    }
}
