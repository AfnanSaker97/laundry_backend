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
class OrderItemController extends BaseController
{
    public function index()
    {
        $userId = Auth::id();
        // Adjust the pagination size as needed      
        $Orders= Order::with(['user','OrderItems.LaundryPrice','address'])
       ->where('user_id',  $userId )->orderByDesc('created_at')->get();
       
       return $this->sendResponse($Orders, 'order fetched successfully.');
    }



    public function store(Request $request)
    {
    
        $validator = Validator::make($request->all(), [
            'laundry_id' => 'required|exists:laundries,id',
            'address_id' => 'required|exists:addresses,id',
            'laundry_price_id' => 'required|exists:laundry_prices,id',
            'quantity' => 'required|numeric',
            'note' => 'nullable',
       
        ]);
 
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());       
        }
        // Retrieve the product and current user ID
        $laundryPrice = LaundryPrice::find($request->laundry_price_id);
 
        $userId = Auth::id();
      
        $date = Carbon::now()->format('Y-m-d H:i:s');
      
        if (!$laundryPrice) {
            return $this->sendError('message', 'The selected laundryPrice id is invalid.');
        }
    try {
        DB::beginTransaction();
    

        $order = Order::create([
            'laundry_id' =>  $request->laundry_id,
            'user_id' => $userId,
            'address_id' => $request->address_id,
            'order_date' => $date,
            'pickup_time' => $date,
            'delivery_time' => $date,
            'note' => $request->note?? '0',
        ]);
 
            // Create a new cart item
            $orderItem = OrderItem::create([
                'quantity' =>  $request->quantity,
                'user_id' => $userId,
                'laundry_price_id' => $request->laundry_price_id,
                'price' => $laundryPrice->price,
                'sub_total_price' =>$laundryPrice->price * $request->quantity,
                'order_id' =>$order->id,
            ]);
         
            $cartItemsTotal = OrderItem::where('order_id', $order->id)->where('user_id', $userId)
            ->sum('sub_total_price');
            $order->total_price = $cartItemsTotal;
            $order->save();
    
        DB::commit();

        return $this->sendResponse($order, 'order successfully.');
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
}
