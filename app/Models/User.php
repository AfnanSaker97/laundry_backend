<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class User extends Authenticatable
{
    use HasFactory, Notifiable;
    use HasApiTokens;
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
    ];



    public function addresses()
    {
        return $this->hasMany(Address::class);
    }


    public function orders()
    {
        return $this->hasMany(Order::class);
    }


    public function advertisements()
{
    return $this->belongsToMany(Advertisement::class, 'user_advertisement_points')
                ->withPivot('points')
                ->withTimestamps();
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
