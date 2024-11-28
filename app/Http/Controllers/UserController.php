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
         
   
    $query = User::where('user_type_id', $userTypeId);
    if ($request->has('page') && $request->page == 0) {
        $users = $query->get(); 
    } else {
        $users = $query->paginate(10);
    }
        return $this->sendResponse($users, 'Users fetched successfully.');
    }

    public function show(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
            ]);
    
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors()->all());
            }
    
            $admin = Auth::user();
            $userId = $request->user_id;
    
            $user = User::select('id', 'name', 'email', 'email_verified_at', 'photo', 'points_wallet')
                ->with(['addresses' => function ($query) {
                    $query->select('id', 'user_id', 'address_line_1', 'address_line_2', 'country', 'city', 'postcode', 'contact_number', 'full_name');
                }])
                ->find($userId);
    
            if ($admin->user_type_id == 4) { 
                $orders = $user->orders()->select(
                    'id', 'pickup_time', 'delivery_time', 'status', 'user_id', 'order_date',
                    'base_cost', 'paid', 'note', 'laundry_id', 'order_type_id', 'point',
                    'order_number', 'type_order'
                )->get();
            } elseif ($admin->user_type_id == 1) {
                $orders = $user->orders()->where('laundry_id', $admin->laundry->id)->select(
                    'id', 'pickup_time', 'delivery_time', 'status', 'user_id', 'order_date',
                    'base_cost', 'paid', 'note', 'laundry_id', 'order_type_id', 'point',
                    'order_number', 'type_order'
                )->get();
            } else {
                $orders = collect(); 
            }
    
           
            $user->setRelation('orders', $orders);
    
            return $this->sendResponse($user, 'Users fetched successfully.');
    
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    


    public function search(Request $request)
    {
        try{
        $user = Auth::user(); 
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string',
            'status_id' => 'required|in:1,2', 
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());
        }
        $query = User::query();
        if ($user->user_type_id == 4) {
     
        $query->where('user_type_id', $request->status_id);
    } elseif ($user->user_type_id == 1) {
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
    }catch (\Throwable $th) {
        return response()->json([
            'status' => false,
            'message' => $th->getMessage()
        ], 500);
    }
    }
}
