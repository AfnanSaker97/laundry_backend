<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'item_id',
        'quantity',
        'price',
        'sub_total_price',
        'isChecked',
        'note',
        'order_id',
      
    ];


    public function LaundryItem()
    {
        return $this->belongsTo(LaundryItem::class);
    }
}
