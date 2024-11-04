<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddressLaundry extends Model
{
    use HasFactory;
    protected $fillable = [
        'laundry_id',
        'city',
        'address_line_1',
        'lat',
        'lng',
        'is_primary'
    ];
}
