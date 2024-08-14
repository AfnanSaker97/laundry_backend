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
    public function index()
    {
        $userId = Auth::id();
        // Adjust the pagination size as needed      
        $Orders= Order::with(['user','OrderItems.LaundryPrice','address'])
         ->orderByDesc('created_at')->get();
       
       return $this->sendResponse($Orders, 'order fetched successfully.');
    }

     
    public function store(Request $request)
    {   

        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',  
            'delivery_date'=> 'required|date',
            'car_id' => 'required|exists:cars,id', 
            ]);
           
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors()->all());       
            }
            $pickup_time = Carbon::parse($request->delivery_date);

            // Add 24 hours to the pickup time
            $pickup_time->addHours(24);
            
            // Convert to a string format
            $delivery_time = $pickup_time->toDateTimeString();
            
          $order = Order::findOrFail($request->order_id);
          $order->pickup_time= $request->delivery_date;
          $order->delivery_time=  $delivery_time;
          $order->car_id= $request->car_id;
          $order->status='confirmed'; 
          $order->save();
          return $this->sendResponse($order, 'order updated successfully.');
        }


    public function filterOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status_id' => 'required|in:1,2,3,4,5,6', // Add validation for allowed status_id values
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());       
        }
        
        $status = [
            1 => 'pending',
            2 => 'confirmed',
            3 => 'Processing',
            4 => 'Shipped',
            5 => 'Delivered',
            6 => 'Cancelled',
        ];
        $orders = Order::with(['user','address','Laundry','OrderItems','OrderItems.LaundryPrice'])
        ->where('status', $status[$request->status_id])
        ->get();

        return $this->sendResponse($orders, 'orders fetched successfully.');
    }

    
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


      


        
public function getOrderByProximity(Request $request)
{
 
    try {
    $user = Auth::user();
   // $driverLatitude =  $user ->lat;
   // $driverLongitude =  $user ->lng;

    
    $orders = DB::table('orders')
    ->join('addresses', 'orders.address_id', '=', 'addresses.id')
    ->join('users', 'orders.user_id', '=', 'users.id')
    ->join('cars', 'orders.car_id', '=', 'cars.id')
    ->select(
        'orders.id',
        'orders.total_price',
        'orders.note',
        'users.first_name',
        'users.last_name',
        'addresses.lat',
        'addresses.lng',
        DB::raw("( 6371 * acos( cos( radians(cars.lat) ) * cos( radians(addresses.lat) ) * cos( radians(addresses.lng) - radians(cars.lng) ) + sin( radians(cars.lat) ) * sin( radians(addresses.lat) ) ) ) AS distance")  )
    ->orderBy('distance')
    ->get();
    return $this->sendResponse($orders, 'Laundries fetched successfully.');
} catch (\Exception $e) {
    DB::rollBack();
    // Log error and return empty array
    return response()->json(['error' =>  $e->getMessage()], 500);
  
}
    }   
}


