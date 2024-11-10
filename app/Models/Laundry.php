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
        'admin_id',
        'point',
        'isActive',
        'urgent'
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
   

    public function advertisement()
    {
        return $this->hasMany(Advertisement::class);
    }
   

    public function price()
    {
        return $this->hasMany(Price::class);
    }
    public function addresses()
{
    return $this->hasOne(AddressLaundry::class,);
}


  

    public function services()
    {
      
        return $this->belongsToMany(Service::class, 'prices')
        ->withPivot('price');
      
    }



    public function drivers()
    {
        // علاقة المغسلة مع السائقين (واحد إلى عدة)
        return $this->hasMany(Driver::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
