<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderCoupon extends Model
{
    use HasFactory;
    protected $fillable = [
        'coupon_id',
        'order_id',
        'value',

    ];
    public function coupon()
    {
        return $this->belongsTo(Coupon::class,'coupon_id');
    }
    public function order()
    {
        return $this->belongsTo(Order::class,'order_id');
    }
}
