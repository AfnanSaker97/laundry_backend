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
        'email',
        'phone_number',
        'country',
        'city',
        'address_line_1',
        'lat',
        'lng',
        'admin_id'
    ];


    public function prices()
    {
        return $this->hasMany(LaundryPrice::class);
    }
}
