<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;
    protected $fillable = [
        'name_ar',
        'name_en',
 
    ];

    public function Laundries()
    {
        return $this->belongsToMany(Laundry::class);
    }
}
