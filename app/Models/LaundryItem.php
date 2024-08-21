<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaundryItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'item_type_en',
        'item_type_ar',
    
       
    ];

  
    public function Laundry()
    {
      
        return $this->belongsToMany(Laundry::class, 'prices')
        ->withPivot('price');
      
    }
}
