<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Laundry;
use App\Models\Car;
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
        $Orders= Order::with(['user','OrderItems.LaundryItem','address'])
         ->orderByDesc('created_at')->get();
       
       return $this->sendResponse($Orders, 'order fetched successfully.');
    }


    public function MyOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status_id' => 'required|in:1,2,3', // Add validation for allowed status_id values
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());       
        }
        try {
        $userId = Auth::id();

        // Define the start and end of the current day
        $startOfDay = Carbon::now()->startOfDay(); // 00:00:00 of today
        $endOfDay = Carbon::now()->endOfDay(); // 23:59:59 of today

         if($request->status_id ==1)
         {
  // Fetch orders created today for the authenticated user where Laundry.admin_id matches
        $orders = Order::with(['user', 'OrderItems.LaundryItem', 'address','Laundry','OrderType'])
            ->whereBetween('order_date', [$startOfDay, $endOfDay]) // Filter by today's date
         //   ->where('status','pending')
            ->whereHas('Laundry', function ($query) use ($userId) {
                $query->where('admin_id', $userId); // Filter by Laundry's admin_id
            })
            ->orderByDesc('order_date')
            ->get();

         }
         //غير مباشر
      if($request->status_id ==2)
      {
         // Fetch orders created today for the authenticated user where Laundry.admin_id matches
         $orders = Order::with(['user', 'OrderItems.LaundryItem', 'address','Laundry','OrderType'])
         ->whereBetween('order_date', [$startOfDay, $endOfDay]) // Filter by today's date
       //  ->where('status','pending')
         ->where('order_type_id','1')
         ->whereHas('Laundry', function ($query) use ($userId) {
             $query->where('admin_id', $userId); // Filter by Laundry's admin_id
         })
         ->orderByDesc('order_date')
         ->get();
      }
      //مباشر
      if($request->status_id ==3)
      {
         // Fetch orders created today for the authenticated user where Laundry.admin_id matches
         $orders = Order::with(['user', 'OrderItems.LaundryItem', 'address','Laundry','OrderType'])
         ->whereBetween('order_date', [$startOfDay, $endOfDay]) // Filter by today's date
       //  ->where('status','pending')
         ->where('order_type_id','2')
         ->whereHas('Laundry', function ($query) use ($userId) {
             $query->where('admin_id', $userId); // Filter by Laundry's admin_id
         })
         ->orderByDesc('order_date')
         ->get();
      }
       
       return $this->sendResponse($orders, 'order fetched successfully.');
    }catch (\Throwable $th) {
        return response()->json([
            'status' => false,
            'message' => $th->getMessage()
        ], 500);
    }
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
            $pickup_time->addHours(1);
            
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
    


        
    public function filterMyOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status_id' => 'required|in:1,2,3', // Add validation for allowed status_id values
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());       
        }
        try {
        $userId = Auth::id();

    
         if($request->status_id ==1)
         {
  // Fetch orders created today for the authenticated user where Laundry.admin_id matches
        $orders = Order::with(['user', 'OrderItems.LaundryItem', 'address','Laundry','OrderType'])

            ->whereHas('Laundry', function ($query) use ($userId) {
                $query->where('admin_id', $userId); // Filter by Laundry's admin_id
            })
            ->orderByDesc('order_date')
            ->get();

         }
         //غير مباشر
      if($request->status_id ==2)
      {
         // Fetch orders created today for the authenticated user where Laundry.admin_id matches
         $orders = Order::with(['user', 'OrderItems.LaundryItem', 'address','Laundry','OrderType'])
       
         ->where('order_type_id','1')
         ->whereHas('Laundry', function ($query) use ($userId) {
             $query->where('admin_id', $userId); // Filter by Laundry's admin_id
         })
         ->orderByDesc('order_date')
         ->get();
      }
      //مباشر
      if($request->status_id ==3)
      {
         // Fetch orders created today for the authenticated user where Laundry.admin_id matches
         $orders = Order::with(['user', 'OrderItems.LaundryItem', 'address','Laundry','OrderType'])
         ->where('order_type_id','2')
         ->whereHas('Laundry', function ($query) use ($userId) {
             $query->where('admin_id', $userId); // Filter by Laundry's admin_id
         })
         ->orderByDesc('order_date')
         ->get();
      }
       
       return $this->sendResponse($orders, 'order fetched successfully.');
    }catch (\Throwable $th) {
        return response()->json([
            'status' => false,
            'message' => $th->getMessage()
        ], 500);
    }  }


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
        $orders = Order::with(['user','address','Laundry','OrderItems','OrderItems.LaundryItem'])
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
            $order = Order::with(['user','address','Laundry','OrderType','OrderItems','OrderItems.LaundryPrice'])->findOrFail($request->order_id);
        
           $Delivery_cost=$order->total_price -$order->base_cost;
           $order['delivery_cost'] =  $Delivery_cost;
     
            $order['user'] =  $order;
       
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
        'users.name',
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



    public function getTotal()
{
    try {
        $userId = Auth::id();
        $laundry =Laundry::where('admin_id',$userId)->first();
     
        // Define the start and end of the current day
        $startOfDay = Carbon::now()->startOfDay(); // 00:00:00 of today
        $endOfDay = Carbon::now()->endOfDay(); // 23:59:59 of today

        // حساب المجموع الكلي للطلبات
        $totalOrdersToday = Order::where('laundry_id',$laundry->id)->whereBetween('order_date', [$startOfDay, $endOfDay])->count();
        $totalOrders = Order::where('laundry_id',$laundry->id)->count();
        $pendingOrders = Order::where('laundry_id',$laundry->id)->where('status','pending')->count();
        $carservice = Car::where('laundry_id',$laundry->id)->where('status','1')->count();
        
        $data = [
            'orders_for_today' => $totalOrdersToday,
            'totalOrders' => $totalOrders,
            'pendingOrders' => $pendingOrders,
            'carservice' => $carservice,
            'incomeThisMonth'=>25,
        ];
        return $this->sendResponse([$data], 'Total orders fetched successfully.');
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

}


