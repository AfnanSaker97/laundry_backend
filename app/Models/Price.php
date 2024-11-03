<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    use HasFactory;
    protected $fillable = [
        'laundry_item_id',
        'laundry_id',
        'service_id',
        'price',
       
    ];

    public function laundry()
    {
        return $this->belongsTo(Laundry::class);
    }

    public function laundryItem()
    {
        return $this->belongsTo(LaundryItem::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
   
}
