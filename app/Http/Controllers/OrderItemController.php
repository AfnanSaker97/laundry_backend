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
        'ids' => 'required|array',
        'ids.*.price_id' => 'required|exists:laundry_prices,id',
        'ids.*.quantity' => 'required|integer|min:1',
        'order_type_id' => 'required|exists:order_types,id',
        'note' => 'nullable|string',
    ]);
       
   
 
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());       
        }
        $order = null; 
        try {
            DB::transaction(function () use ($request, &$order){
                $now = Carbon::now('Etc/GMT-3');
                $pickupTime = $now->copy()->addHour();
                $deliveryTime = $pickupTime->copy()->addDay();
    
               
                $laundry = Laundry::findOrFail($request->laundry_id);
                $address = Address::findOrFail($request->address_id);
                $orderType = OrderType::findOrFail($request->order_type_id);
                $userId = Auth::id();
    
                // Calculate distance
                $distance = round($this->calculateDistance($laundry->lat, $laundry->lng, $address->lat, $address->lng), 1);
    
                // Create the order
                $order = Order::create([
                    'laundry_id' => $request->laundry_id,
                    'user_id' => $userId,
                    'address_id' => $request->address_id,
                    'order_date' => $now,
                    'pickup_time' => $pickupTime,
                    'delivery_time' => $deliveryTime,
                    'note' => $request->note ?? '0',
                    'order_type_id' => $request->order_type_id,
                    'distance' => $distance,
                ]);
                foreach ($request->ids as $item) {
                    $laundryPrice = LaundryPrice::findOrFail($item['price_id']);
                    
                    // Calculate subtotal
                    $subTotalPrice = $laundryPrice->price * $item['quantity'];
    
                    // Create order item
                    OrderItem::create([
                        'quantity' => $item['quantity'],
                        'user_id' => $userId,
                        'laundry_price_id' => $item['price_id'],
                        'price' => $laundryPrice->price,
                        'sub_total_price' => $subTotalPrice,
                        'order_id' => $order->id,
                    ]);
                }

    
                // Calculate cart total
                $cartItemsTotal = OrderItem::where('order_id', $order->id)
                                           ->where('user_id', $userId)
                                           ->sum('sub_total_price');
    
                // Calculate delivery cost and total price
                $orderTypeKmPrice = OrderType::findOrFail(1)->price;
                $costDeliverKm = $orderTypeKmPrice * $distance;
                $costDeliver = $costDeliverKm;
    
                if ($request->order_type_id == 2) {
                    $costDeliver += $orderType->price; // Add the specific order type's price
                }
    
                $totalPrice = $cartItemsTotal + $costDeliver;
    
                // Update order with calculated costs
                $order->update([
                    'base_cost' => $cartItemsTotal,
                    'total_price' => $totalPrice,
                ]);
                return $order;
            });
      
          
        return $this->sendResponse($order, 'Order created successfully.');
        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function totalPrice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'laundry_id' => 'required|exists:laundries,id',
            'address_id' => 'required|exists:addresses,id',
            'ids' => 'required|array',
            'ids.*.price_id' => 'required|exists:laundry_prices,id',
            'ids.*.quantity' => 'required|integer|min:1',
            'order_type_id' => 'required|exists:order_types,id',
        ]);
    
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());       
        }
    
        try {
            $orderType = OrderType::findOrFail($request->order_type_id);
            $userId = Auth::id();
    
            $totalPrice = 0;
    
            foreach ($request->ids as $item) {
                $laundryPrice = LaundryPrice::findOrFail($item['price_id']);
                
                // حساب السعر الفرعي
                $subTotalPrice = $laundryPrice->price * $item['quantity'];
    
                // جمع الأسعار الفرعية لحساب السعر الكلي
                $totalPrice += $subTotalPrice;
            }
    
            // يمكنك استخدام $totalPrice في إنشاء الطلب أو إرجاعه مباشرة
            return $this->sendResponse(['total_price' => $totalPrice], 'Total price calculated successfully.');
        } catch (\Exception $e) {
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
