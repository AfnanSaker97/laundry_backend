<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Laundry;
use App\Models\LaundryPrice;
use App\Models\OrderItem;
use App\Models\Order;
use App\Models\MySession;
use Carbon\Carbon;
use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Support\Facades\DB;
use Validator;
use Auth;
class OrderController extends BaseController
{
    
    public function OrderDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',  
            ]);
           
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors()->all());       
            }
            $order = Order::with(['user','address','Laundry','OrderItems','OrderItems.LaundryPrice'])->findOrFail($request->order_id);
            return $this->sendResponse($order, 'order fetched successfully.');
        }

}
