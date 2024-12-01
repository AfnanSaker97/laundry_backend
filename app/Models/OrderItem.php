<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class OrderItem extends Model
{
    use HasFactory ,SoftDeletes ;
    protected $fillable = [
        'user_id',
        'laundry_item_id',
        'service_id',
        'quantity',
        'price',
        'sub_total_price',
        'isChecked',
        'note',
        'order_id',
        'deleted_at'
      
    ];


    public function LaundryItem()
    {
        return $this->belongsTo(LaundryItem::class);
    }

    
    public function Service()
    {
        return $this->belongsTo(Service::class);
    }
}
