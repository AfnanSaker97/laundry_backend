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
            'page' => 'nullable|boolean' 
        ]);
    
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());
        }
    
        $userTypeId = $request->status_id;
         
    // استرجاع المستخدمين بناءً على نوع المستخدم
    $query = User::where('user_type_id', $userTypeId);
    if ($request->has('page') && $request->page == 0) {
        $users = $query->get(); 
    } else {
        $users = $query->paginate(10);
    }
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
        if ($user->user_type_id == 4) {
        // Super Admin: Filter by user_type_id and optionally by name or email
        $query->where('user_type_id', $request->status_id);
    } elseif ($user->user_type_id == 1) {
        // Admin: Filter by user_type_id and associated laundry orders
        $query->where('user_type_id', $request->status_id)
              ->whereHas('orders', function ($q) use ($user) {
                  $q->where('laundry_id', $user->laundry->id);
              });
    }
    if ($request->filled('name')) {
        $query->where(function ($q) use ($request) {
            $q->where('name', 'like', '%' . $request->name . '%')
              ->orWhere('email', 'like', '%' . $request->name . '%');
        });
    }

       
        // Fetch the users with pagination (optional)
        $users = $query->paginate(10);
        return $this->sendResponse($users, 'Users fetched successfully.');
   
    }
}
