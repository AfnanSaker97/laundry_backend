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
use App\Models\Price;
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
        $Orders= Order::with(['user','OrderItems.LaundryItem','address'])
       ->where('user_id',  $userId )->orderByDesc('created_at')->get();
       
       return $this->sendResponse($Orders, 'order fetched successfully.');
    }



    public function store(Request $request)
    {
        try {
        $validator = Validator::make($request->all(), [
        'laundry_id' => 'required|exists:laundries,id',
        'address_id' => 'required|exists:addresses,id',
        'ids' => 'required|array',
        'ids.*.item_id' => 'required|exists:laundry_items,id',
        'ids.*.service_id' => 'required|exists:services,id',
        'ids.*.quantity' => 'required|integer|min:1',
        'order_type_id' => 'required|exists:order_types,id',
        'note' => 'nullable|string',
        'pickup_time'=> 'nullable|date',
    ]);
       

 
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());       
        }
        $order = DB::transaction(function () use ($request) {
            $now = Carbon::now('Asia/Dubai');
            $pickupTime = null;
            $deliveryTime = null;

            if ($request->order_type_id == 2) {
                $pickupTime = Carbon::parse($request->pickup_time);
                $deliveryTime = $pickupTime->copy()->addDay();
            } elseif ($request->order_type_id == 1) {
                $pickupTime = $now->copy()->addDay();
                $deliveryTime = $pickupTime->copy()->addDay();
            }

            $laundry = Laundry::findOrFail($request->laundry_id);
            $userAddress = Address::findOrFail($request->address_id);
            $orderType = OrderType::findOrFail($request->order_type_id);
            $user = Auth::user();
         

            $order_type = ($user->user_type_id == '4' || $user->user_type_id == '1') ? 'web' : 'app';

          //  $distance = round($this->calculateDistance($nearestLaundryAddress->lat, $nearestLaundryAddress->lng, $userAddress->lat, $userAddress->lng), 1);
           $order = Order::create([
                'laundry_id' => $request->laundry_id,
                'user_id' => $user->id,
                'address_id' => $request->address_id,
                'order_date' => $now,
                'pickup_time' => $pickupTime,
                'delivery_time' => $deliveryTime,
                'note' => $request->note ?? '0',
                'type_order' => $order_type,
                'order_type_id' => $request->order_type_id,
             //   'distance' => $distance,
                'order_number' => $this->generateOrderId(),
            ]);

            $cartItemsTotal = 0;
            foreach ($request->ids as $item) {
                $laundryItem = $laundry->LaundryItem()->where('laundry_items.id', $item['item_id'])->first();
                $service = $laundry->services()->where('services.id', $item['service_id'])->first();

                if (!$laundryItem || !$service) {
                    throw new \Exception('Invalid item or service for this laundry.');
                }

                $priceRecord = Price::where('laundry_item_id', $laundryItem->id)
                    ->where('laundry_id', $laundry->id)
                    ->where('service_id', $service->id)
                    ->where('order_type_id',$request->order_type_id)
                    ->first();
                
                   
                if (!$priceRecord) {
                    throw new \Exception('Price not found for specified item and service.');
                }

                $subTotalPrice = $priceRecord->price * $item['quantity'];
                $cartItemsTotal += $subTotalPrice;
                OrderItem::create([
                    'quantity' => $item['quantity'],
                    'user_id' => $user->id,
                    'laundry_item_id' => $item['item_id'],
                    'service_id' => $item['service_id'],
                    'price' => $priceRecord->price,
                    'sub_total_price' => $subTotalPrice,
                    'order_id' => $order->id,
                ]);
            }

    
            if ($request->order_type_id == 1) {
                $order->update([
                    'status' => 'Confirmed',
                   // 'point' => $laundry->point,
                    'car_id' => 1,
                ]);
             //   $user->increment('points_wallet', $order->point);
            }
            $totalPrice = $cartItemsTotal ;

            $order->update([
                'base_cost' => $cartItemsTotal,
               // 'total_price' => $totalPrice,
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
            'ids.*.item_id' => 'required|exists:laundry_items,id',
            'ids.*.quantity' => 'required|integer|min:1',
            'order_type_id' => 'required|exists:order_types,id',
        ]);
       
    
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());       
        }
    
        try {
            $orderType = OrderType::findOrFail($request->order_type_id);
            $userId = Auth::id();
            $laundry = Laundry::findOrFail($request->laundry_id);
              
            $totalPrice = 0;
    
            foreach ($request->ids as $item) {
                $laundryItem = $laundry->LaundryItem()->where('laundry_items.id', $item['item_id'])->first();
                
                if (!$laundryItem) {
                    throw new \Exception('Item not found for this laundry.');
                }
                $price = $laundryItem->pivot->price;
                // Calculate subtotal
                $subTotalPrice = $price * $item['quantity'];

              
                

    
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

private function generateOrderId()
{
 // Generate a random 5-digit number
 $randomNumber = random_int(10000, 99999);
    
 // Return the order ID with 'O-' prefix
 return 'Order-' . $randomNumber;
}

}
