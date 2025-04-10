<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'status',
        'total_price',
        'payment_method',
        'payment_status',
        'transaction_id',



    ];
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function order_product()
    {
        return $this->hasMany(OrderProduct::class);
    }
    public function Coupon_Order()
    {
        return $this->hasMany(OrderCoupon::class);
    }
}
