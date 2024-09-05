<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Address extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'address_line_1','address_line_2','email','country','city',
    'postcode','contact_number','full_name','address', 'lat','lng','isActive'];



    
}


