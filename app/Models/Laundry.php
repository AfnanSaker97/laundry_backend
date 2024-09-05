<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laundry extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name_en',
        'name_ar',
        'description_ar',
        'description_en',
        'email',
        'phone_number',
        'country',
        'city',
        'address_line_1',
        'lat',
        'lng',
        'admin_id',
        'point',
        'isActive'
    ];
   public function LaundryItem()
    {
      
        return $this->belongsToMany(LaundryItem::class, 'prices')
        ->withPivot('price');
      
    }


    public function LaundryMedia()
    {
        return $this->hasMany(LaundryMedia::class);
    }
   



    public function services()
    {
      
        return $this->belongsToMany(Service::class, 'laundry_service');
      
    }
}
