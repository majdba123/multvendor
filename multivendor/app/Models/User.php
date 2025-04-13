<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'google_id',
        'facebook_id',
        'phone',
        'email',
        'otp',
        'type',
        'password',
    ];


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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function Profile()
    {
        return $this->hasOne(profile::class);
    }
    public function vendor()
    {
        return $this->hasOne(Vendor::class);
    }
    public function afiliate()
    {
        return $this->hasOne(Afiliate::class);
    }
    public function driver()
    {
        return $this->hasOne(Driver::class);
    }
    public function favourite_user()
    {
        return $this->hasMany(Favourite::class);
    }
    public function rateing()
    {
        return $this->hasMany(Rating::class);
    }
    public function order()
    {
        return $this->hasMany(Order::class);
    }
    public function answer_rating()
    {
        return $this->hasMany(AvswerRating::class);
    }
}
