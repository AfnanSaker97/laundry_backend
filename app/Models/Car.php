<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use HasFactory;
      
    protected $fillable = [
        'driver_id',
        'number_car',
        'status',
        'laundry_id',
        'lat',
        'lng'
    ];

    public function driver()
    {
         
        return $this->belongsTo(User::class,'driver_id');
    }

    public function Laundry()
    {
         
        return $this->belongsTo(Laundry::class,'laundry_id');
    }
}
