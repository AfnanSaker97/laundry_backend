<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaundryMedia extends Model
{
    use HasFactory;
    protected $fillable = [
       'laundry_id',
        'url_image'
 
    ];
}
