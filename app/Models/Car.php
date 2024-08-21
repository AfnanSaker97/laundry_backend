<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use HasFactory;
      
    protected $fillable = [
       // 'driver_id',
        'driver_phone',
        'status',
        'laundry_id',
        'lat',
        'lng'
    ];

    public function driver()
    {
         
        return $this->belongsTo(User::class,'driver_id');
    }
}
