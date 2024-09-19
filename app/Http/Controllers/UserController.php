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
        
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string',
            'status_id' => 'required|in:1,2,3', 
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());
        }
        $userTypeId = $request->status_id;
        // Build the query based on the request input
        $query = User::query();
        $query->where('user_type_id', $userTypeId);  
        
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
     
       
        // Fetch the users with pagination (optional)
        $users = $query->paginate(10);
        return $this->sendResponse($users, 'Users fetched successfully.');
   
    }
}
