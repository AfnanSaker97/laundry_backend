<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;
          
    protected $fillable = [
        'user_id',
        'laundry_id',
    ];

    public function user()
    {
        // علاقة السائق مع المستخدم (سائق هو مستخدم)
        return $this->belongsTo(User::class, 'user_id');
    }

    public function laundry()
    {
        // علاقة السائق مع المغسلة
        return $this->belongsTo(Laundry::class);
    }

    public function car()
    {
        // علاقة السائق مع السيارة (واحد إلى واحد)
        return $this->hasOne(Car::class);
    }
}
