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
         ->orderByDesc('created_at')->paginate(10);
       
       return $this->sendResponse($Orders, 'order fetched successfully.');
    }


    
    public function OrderByLaundryId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'laundry_id' => 'required|exists:laundries,id', // Add validation for allowed status_id values
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());       
        }
        try {
      
        // Initialize the query builder for orders
        $query = Order::with(['user','address', 'OrderItems.LaundryItem', 'Laundry.addresses', 'OrderType'])
          ->where('laundry_id',$request->laundry_id)->orderByDesc('order_date');


     // Fetch orders with pagination
        $orders = $query->paginate(10);
       return $this->sendResponse($orders, 'order fetched successfully.');
    }catch (\Throwable $th) {
        return response()->json([
            'status' => false,
            'message' => $th->getMessage()
        ], 500);
    }
    }


    public function FilterOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'laundry_id' => 'nullable|exists:laundries,id', // Add validation for allowed status_id values
            'status_id' => 'nullable|in:1,2,3,4', 
            'order_type_id' => 'nullable|exists:order_types,id',
            'type_order' => 'nullable|in:app,web',
            'from_date' => 'nullable|date', 
            'to_date' => 'nullable|date|after_or_equal:from_date',       
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());       
        }
        try {
            $status = [
                1 => 'pending',
                2 => 'Request',
                3 => 'Confirmed',
                4 => 'Cancelled',
            ];

        // Initialize the query builder for orders
        $query = Order::with(['user', 'OrderItems.LaundryItem', 'address', 'Laundry.addresses', 'OrderType'])
          ->orderByDesc('order_date');
             // Apply filters conditionally
             $query->when($request->laundry_id, function ($q) use ($request) {
                $q->where('laundry_id', $request->laundry_id);
            });
       // Filter orders by type_order (app/web) if provided
        $query->when($request->type_order, function ($q) use ($request) {
        $q->where('type_order', $request->type_order);
      });
    
            $query->when($request->order_type_id, function ($q) use ($request) {
                $q->where('order_type_id', $request->order_type_id);
            });

              // الفلترة حسب status_id (الحالة مثل pending, confirmed, cancelled)
        $query->when($request->status_id, function ($q) use ($request, $status) {
            $q->where('status', $status[$request->status_id]);
        });
      
         // الفلترة حسب التاريخ
         $query->when($request->from_date, function ($q) use ($request) {
            $q->whereDate('order_date', '>=', $request->from_date);
        });

        $query->when($request->to_date, function ($q) use ($request) {
            $q->whereDate('order_date', '<=', $request->to_date);
        });
        
     // Fetch orders with pagination
        $orders = $query->paginate(10);
       return $this->sendResponse($orders, 'order fetched successfully.');
    }catch (\Throwable $th) {
        return response()->json([
            'status' => false,
            'message' => $th->getMessage()
        ], 500);
    }
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
        $user = Auth::user();

        // Define the start and end of the current day
        $startOfDay = Carbon::now()->startOfDay(); // 00:00:00 of today
        $endOfDay = Carbon::now()->endOfDay(); // 23:59:59 of today



        // Initialize the query builder for orders
        $query = Order::with(['user', 'OrderItems.LaundryItem', 'address', 'Laundry.addresses', 'OrderType'])
            ->whereBetween('order_date', [$startOfDay, $endOfDay]) // Filter by today's date
            ->orderByDesc('order_date');
            
 // Apply filters based on status_id
 if ($request->status_id == 1) {
    // Status ID 1: No additional filters
} elseif ($request->status_id == 2) {
    $query->where('order_type_id', '1'); // Filter by order_type_id for غير مباشر
} elseif ($request->status_id == 3) {
    $query->where('order_type_id', '2'); // Filter by order_type_id for مباشر
}

 // Apply user_type_id specific conditions
 if ($user->user_type_id == 1) {
    $query->whereHas('Laundry', function ($query) use ($user) {
        $query->where('admin_id', $user->id); // Filter by Laundry's admin_id
    });
}

// Fetch orders with pagination
$orders = $query->paginate(10);

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
            'status'=> 'required',
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
          $order->status=$request->status; 
          $order->save();

         // Define notification content
     $notificationContent = [
        'title' => 'A response to your request has been sent.',
        'body' => 'Your order #' . $order->id . ' has been updated successfully.',
        'order_id' => $order->id,
    ];
        $user = $order->user; 
        $this->sendNotification($request, $user, $notificationContent);

          return $this->sendResponse($order, 'order updated successfully.');
        }
    


        
    public function filterMyOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status_id' => 'required|in:1,2,3', 
            'laundry_id' => 'nullable|exists:laundries,id',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());       
        }
        try {

            $user = Auth::user();

            $startOfDay = Carbon::now()->startOfDay(); 
            $endOfDay = Carbon::now()->endOfDay(); 
       
            $query = Order::with(['user', 'OrderItems.LaundryItem', 'address', 'Laundry.addresses', 'OrderType'])
              ->orderByDesc('order_date');
                
     if ($request->status_id == 1) {
    
    } elseif ($request->status_id == 2) {
        $query->where('order_type_id', '1'); 
    } elseif ($request->status_id == 3) {
        $query->where('order_type_id', '2'); 
    }
    
    
     if ($user->user_type_id == 1) {
        $query->whereHas('Laundry', function ($query) use ($user) {
            $query->where('admin_id', $user->id); 
        });
    }else{
        if ($request->has('laundry_id')) {
            $query->where('laundry_id', $request->laundry_id);
        } 
    }
   
    $orders = $query->paginate(10);
    
       return $this->sendResponse($orders, 'order fetched successfully.');
    }catch (\Throwable $th) {
        return response()->json([
            'status' => false,
            'message' => $th->getMessage()
        ], 500);
    }  }


   /* public function filterOrder(Request $request)
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
*/
    
    public function OrderDetails(Request $request)
    {   
    
        try{

      
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',  
            ]);
           
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors()->all());       
            }
            $order = Order::with([
            'user:id,name,email,photo,lat,lng,points_wallet', // Directly select only needed fields
            'address:id,address_line_1,address_line_2,country,city,address,postcode,lat,lng,email,contact_number,full_name', 
          
                'Laundry.addresses', 
                'Laundry.LaundryMedia', 
                'OrderType',
                'OrderItems',
                'OrderItems.LaundryItem'
            ])->findOrFail($request->order_id);

            $order->Laundry->LaundryMedia->each(function ($media) {
                $media->makeHidden(['created_at', 'updated_at']);
            });
            $order->Laundry->makeHidden(['created_at', 'updated_at']);
            $order->makeHidden(['created_at', 'updated_at','distance','total_price','address_laundry_id']);
            $order->OrderItems->each(function ($item) {
                $item->makeHidden(['created_at', 'updated_at']);
            });
            $order['user'] =  $order;
       
            return $this->sendResponse($order, 'order fetched successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            // Log error and return empty array
            return response()->json(['error' =>  $e->getMessage()], 500);
          
        }
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
        $user = Auth::user();
    // Define the start and end of the current day
       $startOfDay = Carbon::now()->startOfDay(); // 00:00:00 of today
       $endOfDay = Carbon::now()->endOfDay(); // 23:59:59 of today
       if ($user->user_type_id == 1) {

        $laundry =Laundry::where('admin_id',$user->id)->first();
        // حساب المجموع الكلي للطلبات
        $totalOrdersToday = Order::where('laundry_id',$laundry->id)->whereBetween('order_date', [$startOfDay, $endOfDay])->count();
        $totalOrders = Order::where('laundry_id',$laundry->id)->count();
        $pendingOrders = Order::where('laundry_id',$laundry->id)->where('status','pending')->count();
        $carservice = Car::where('laundry_id',$laundry->id)->where('status','1')->count();
       
    } elseif ($user->user_type_id == 4) {

          $totalOrdersToday = Order::whereBetween('order_date', [$startOfDay, $endOfDay])->count();
          $totalOrders = Order::count();
          $pendingOrders = Order::where('status','pending')->count();
          $carservice = Car::where('status','1')->count();
         
    }

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



public function getOrderStats(Request $request)
{
    $validator = Validator::make($request->all(), [
        'status_id' => 'required|in:1,2,3',
        'start_date' => 'nullable|date|before:end_date', 
        'end_date' => 'nullable|date|before_or_equal:today', 
        ]);
        // Custom validation rule for status_id = 1
  // Custom validation rule based on status_id
  $validator->after(function ($validator) use ($request) {
    $startDate = Carbon::parse($request->start_date);
    $endDate = Carbon::parse($request->end_date);

    if ($request->status_id == 1) {
       
        $dateDifference = $startDate->diffInDays($endDate);
        if ($dateDifference >= 30) {
            $validator->errors()->add('end_date', 'The difference between start_date and end_date must be less than 30 days when status_id is 1.');
        }
    }

    if ($request->status_id == 2) {
        $dateDifferenceInMonths = $startDate->diffInMonths($endDate);
        if ($dateDifferenceInMonths >= 12) {
            $validator->errors()->add('end_date', 'The difference between start_date and end_date must be less than 12 months when status_id is 2.');
        }
    }

    if ($request->status_id == 3) {
        $dateDifferenceInYears = $startDate->diffInYears($endDate);
        if ($dateDifferenceInYears >= 5) {
            $validator->errors()->add('end_date', 'The difference between start_date and end_date must be less than 5 years when status_id is 3.');
        }
    }
});
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());       
        }
    $userId=Auth::id();
  
    $laundry =Laundry::where('admin_id',$userId)->first();
     // التحقق من وجود المغسلة
     if (!$laundry) {
        return $this->sendError('Laundry not found.', ['Laundry not found for the given admin.']);
    }
    // الحصول على التاريخ من الطلب أو تحديد فترة افتراضية
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');
    if($request->status_id ==1)
    {
        if(!$request->start_date && !$request->end_date)
        {
            $startDate = Carbon::now()->startOfMonth()->toDateString();
            $endDate = Carbon::now()->endOfMonth()->toDateString();
        
        }

    // جمع البيانات من قاعدة البيانات
    $orders = Order::where('laundry_id', $laundry->id)
    ->whereBetween('order_date', [$startDate, $endDate])
        
     ->selectRaw('DATE(order_date) as date, COUNT(*) as count')
        ->groupBy('date')
        ->orderBy('date', 'asc')
        ->get();
            
    }
    if($request->status_id ==2)
    {
   
    // جمع البيانات من قاعدة البيانات
    $orders = Order::whereBetween('order_date', [$startDate, $endDate])
        ->where('laundry_id', $laundry->id)
        ->selectRaw('YEAR(order_date) as year, MONTH(order_date) as month, COUNT(*) as count')
        ->groupBy('year', 'month')
        ->orderBy('year', 'asc')
        ->orderBy('month', 'asc')
        ->get()
        ->map(function ($order) {
            return [
                'year' => $order->year,
                'month' => $order->month,
                'count' => $order->count,
            ];
        });
    }

    if($request->status_id ==3)
    {
   
    // جمع البيانات من قاعدة البيانات حسب السنة
    $orders = Order::whereBetween('order_date', [$startDate, $endDate])
        ->where('laundry_id', $laundry->id)
        ->selectRaw('YEAR(order_date) as year, COUNT(*) as count')
        ->groupBy('year')
        ->orderBy('year', 'asc')
        ->get()
        ->map(function ($order) {
            return [
                'year' => $order->year,
                'count' => $order->count,
            ];
        });
    }
    return $this->sendResponse($orders, ' orders fetched successfully.');

}




public function filterMyOrderUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status_id' => 'required|in:1,2,3,4', // Add validation for allowed status_id values
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());       
        }
        
        $status = [
            1 => 'pending',
            2 => 'request',
            3 => 'confirmed',
            4 => 'cancelled',
        ];
        $orders = Order::with(['user','address','Laundry.addresses','OrderItems.LaundryItem'])
        ->where('status', $status[$request->status_id])
        ->get();

        return $this->sendResponse($orders, 'orders fetched successfully.');
    }




    
public function ordersUser(Request $request)
{
    try {
        $user = Auth::user();
        // Find the address by ID
        $orders = Order::with('Laundry.addresses','OrderType','OrderItems','address')->where('user_id',$user->id)->orderBy('order_date', 'desc')->get();
  // Format the categories data if needed
  return $orders->map(function ($order) {
    return [
        'id' => $order->id,
        'pickup_time' =>$order->pickup_time,
        'delivery_time' => $order->delivery_time ,
        'order_date' => $order->order_date ,
        'status' => $order->status ,
        'base_cost' => $order->base_cost ,
        'total_price' => $order->total_price ,
        'note' => $order->note ,
        'point' => $order->point ,
        'laundry_name_ar' => $order->laundry->name_ar ,
        'laundry_name_en' => $order->laundry->name_en ,
        'addresses' => $order->laundry->addresses ,
        'order_type' => $order->OrderType->type ,
        'order_type_price' => $order->OrderType->price ,
        'order_items' => $order->OrderItems,
        'UserAddress' => $order->address,
    ];
});
    
        return $this->sendResponse($orders,'orders fetched successfully.');

    
    } catch (\Throwable $th) {
        return response()->json([
            'status' => false,
            'message' => $th->getMessage()
        ], 500); 
    
    } 
}



public function search(Request $request)
{
   
    $validator = Validator::make($request->all(), [
        'number' => 'nullable|string', 
    ]);
    if ($validator->fails()) {
        return $this->sendError('Validation Error.', $validator->errors()->all());
    }
    $query = Order::with(['user', 'OrderItems.LaundryItem', 'address', 'Laundry.addresses', 'OrderType'])
    ->orderByDesc('order_date');

    if ($request->has('number')) {
     $query->where('order_number', 'like', '%' . $request->number . '%');
     }
   
    // Fetch the users with pagination (optional)
    $orders = $query->paginate(10);
    return $this->sendResponse($orders, 'Orders fetched successfully.');

}



public function destroy(Request $request)
{ 
    try { 
    $validator = Validator::make($request->all(), [
        'id' => 'required|exists:orders,id', 
    ]);
   
    if ($validator->fails()) {
        return $this->sendError('Validation Error.', $validator->errors()->all());
    }
  
          $order = Order::where('id', $request->id)
                      ->whereNull('deleted_at')
                      ->first();

        
        if (!$order) {
            $msg ='Order already deleted';
            return $this->sendError('Validation Error',[$msg]  );
        }
     
        $order->delete();
        return $this->sendResponse($order, 'Order deleted successfully');
    } catch (\Exception $e) {

        return response()->json([
            'status' => false,
            'message' => 'Order not found or could not be deleted',
            'error' => $e->getMessage() // يمكنك إرجاع الرسالة التفصيلية للاخطاء
        ], Response::HTTP_NOT_FOUND);
    }
}

}


