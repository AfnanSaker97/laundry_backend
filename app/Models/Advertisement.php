<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    use HasFactory;
    protected $fillable = [
        'name_en',
        'name_ar',
        'description_ar',
        'description_en',
        'code',
        'points',
        'status',
        'end_date',
        'laundry_id',
        'start_date'
    ];

    public function users()
{
    return $this->belongsToMany(User::class, 'user_advertisement_points')
                ->withPivot('points')
                ->withTimestamps();
}


public function Media()
{
    return $this->hasMany(AdvertisementMedia::class);
}


  public function laundry()
  {
      return $this->belongsTo(Laundry::class);
  }

}

