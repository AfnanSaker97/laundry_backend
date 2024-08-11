<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaundryPrice extends Model
{
    use HasFactory;
    protected $fillable = [
        'item_type_en',
        'item_type_ar',
        'price',
       
    ];

    public function laundry()
    {
        return $this->belongsTo(Laundry::class);
    }

}
