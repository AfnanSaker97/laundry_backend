<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
class User extends Authenticatable
{
    use HasFactory, Notifiable;
    use HasApiTokens, SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'verification_code',
        'email_verified_at',
        'photo',
        'lat',
        'lng',
        'driver_id',
        'user_type_id',
        'password',
        'device_token',
        'points_wallet'
    ];



    public function addresses()
    {
        return $this->hasMany(Address::class,'user_id');
    }


    public function orders()
    {
        return $this->hasMany(Order::class,'user_id');
    }


    public function advertisements()
{
    return $this->belongsToMany(Advertisement::class, 'user_advertisement_points')
                ->withPivot('points')
                ->withTimestamps();
}
public function laundry()
{
    return $this->hasOne(Laundry::class, 'admin_id');
}

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
