<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laundry extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'country',
        'city',
        'address_line_1',
        'lat',
        'lng'
    ];
}