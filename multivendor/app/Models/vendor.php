<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
    ];


    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }


    public function discount()
    {
        return $this->hasMany(Discount::class);
    }


    public function orders()
    {
        return $this->hasManyThrough(
            OrderProduct::class, // النموذج الوسيط
            Product::class,       // النموذج المرتبط
            'vendor_id',          // المفتاح الأجنبي في جدول المنتجات
            'product_id',         // المفتاح الأجنبي في جدول Order_Product
            'id',                 // المفتاح الأساسي في جدول Vendor
            'id'                  // المفتاح الأساسي في جدول Product
        );
    }




    public function getCompletedOrdersCountAttribute()
    {
        return $this->orders()->where('status', 'complete')->count();
    }

    public function getPendingOrdersCountAttribute()
    {
        return $this->orders()->where('status', 'pending')->count();
    }

    public function getCancelledOrdersCountAttribute()
    {
        return $this->orders()->where('status', 'cancelled')->count();
    }


    public function getTotalSalesAttribute()
    {
        return $this->orders()->where('status', 'complete')->sum('total_price');
    }


    public function getTotalSalesPendingAttribute()
    {
        return $this->orders()->where('status', 'pending')->sum('total_price');
    }




}
