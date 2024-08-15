<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'pickup_time',
        'delivery_time',
        'status',
         'user_id',
        'order_date',
        'total_price',
        'note',
        'laundry_id',
        'order_id',
        'address_id',
          'car_id',
          'order_type_id'
      
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
        return $this->belongsTo(Address::class);
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
