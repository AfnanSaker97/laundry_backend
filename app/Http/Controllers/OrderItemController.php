<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Laundry;
use App\Models\LaundryPrice;
use App\Models\OrderItem;
use App\Models\Order;
use App\Models\MySession;
use App\Models\OrderType;
use App\Models\Address;
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

          //  'array_laundry_price_ids' => 'sometimes|min:1',
            'laundry_price_id' => 'required|exists:laundry_prices,id',
          //  'array_quantity' => 'sometimes|min:1',
            'quantity' => 'required|integer|min:1',

            'order_type_id' => 'required|exists:order_types,id',
            'note' => 'nullable|string',
       
        ]);
 
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());       
        }
        try {
            DB::beginTransaction();
            $now = Carbon::now('Etc/GMT-3');
            $pickupTime = $now->copy()->addHour();
            $deliveryTime = $pickupTime->copy()->addDay();

          $laundryPrice = LaundryPrice::findOrFail($request->laundry_price_id);
          $laundry = Laundry::findOrFail($request->laundry_id);
          $address = Address::findOrFail($request->address_id);
        $orderType = OrderType::findOrFail($request->order_type_id);
        $userId = Auth::id();
 
     

    // حساب المسافة باستخدام دالة calculateDistance
    $distance = $this->calculateDistance($laundry->lat, $laundry->lng, $address->lat, $address->lng);
    $distance = round($distance, 1);
        $order = Order::create([
            'laundry_id' => $request->laundry_id,
            'user_id' => $userId,
            'address_id' => $request->address_id,
            'order_date' => $now,
            'pickup_time' => $pickupTime,
            'delivery_time' => $deliveryTime,
            'note' => $request->note ?? '0',
            'order_type_id' => $request->order_type_id,
            'distance' =>  $distance,
        ]);

           
        $subTotalPrice = $laundryPrice->price * $request->quantity;

        $orderItem = OrderItem::create([
            'quantity' => $request->quantity,
            'user_id' => $userId,
            'laundry_price_id' => $request->laundry_price_id,
            'price' => $laundryPrice->price,
            'sub_total_price' => $subTotalPrice,
            'order_id' => $order->id,
        ]);
        $cartItemsTotal = OrderItem::where('order_id', $order->id)->where('user_id', $userId)
        ->sum('sub_total_price');
        if($request->order_type_id==1)
        {
            $cost_deliver=$orderType->price* $order->distance;
            $totalPrice =  $cartItemsTotal  +   $cost_deliver;
        }
        else
        {
            $orderType_km = OrderType::findOrFail(1)->price;
            
            $cost_deliver=$orderType->price+ $orderType_km*$order->distance;
       
            $totalPrice =  $cartItemsTotal  +   $cost_deliver;
        }
       

        $order->update(['base_cost' => $cartItemsTotal ]);
        $order->update(['total_price' => $totalPrice]);
       

        DB::commit();
    
        return $this->sendResponse($order, 'order successfully.');
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['error' => $e->getMessage()], 500);
    }
}




public function calculateDistance($lat1, $lon1, $lat2, $lon2)
{
    $earthRadius = 6371; // نصف قطر الأرض بالكيلومتر

    $latDelta = deg2rad($lat2 - $lat1);
    $lonDelta = deg2rad($lon2 - $lon1);

    $a = sin($latDelta / 2) * sin($latDelta / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($lonDelta / 2) * sin($lonDelta / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    $distance = $earthRadius * $c;

    return $distance;
}

}
