<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'total_price',
        'status',


    ];
    public function order()
    {
        return $this->belongsTo(Order::class,'order_id');
    }
    public function Product()
    {
        return $this->belongsTo(Product::class,'product_id');
    }
    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }
}
