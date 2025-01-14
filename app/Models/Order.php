<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Order extends Model
{
    use HasFactory ,SoftDeletes ;
    protected $fillable = [
        'pickup_time',
        'delivery_time',
        'status',
         'user_id',
        'order_date',
        'base_cost',
        'total_price',
        'distance',
        'paid',
        'note',
        'laundry_id',
        'order_id',
        'address_id',
          'car_id',
          'order_type_id',
          'point',
          'order_number',
          'type_order',
          'address_laundry_id',
          'deleted_at'
      
    ];

    public function OrderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // An order belongs to an address
    public function address()
    {
        return $this->belongsTo(Address::class,'address_id');
    }


    public function LaundryAddress()
    {
        return $this->belongsTo(AddressLaundry::class,'address_laundry_id');
    }
    public function Laundry()
    {
        return $this->belongsTo(Laundry::class);
    }

    public function OrderType()
    {
        return $this->belongsTo(OrderType::class);
    } 
}
