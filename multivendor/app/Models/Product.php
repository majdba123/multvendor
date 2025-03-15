<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'sub_category_id',
        'name',
        'discription',
        'price',
    ];
    public function subcategory()
    {
        return $this->belongsTo(SubCategory::class,'sub_category_id');
    }
    public function discount()
    {
        return $this->hasOne(Discount::class);
    }
    public function order_product()
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function Favourite_user()
    {
        return $this->morphMany(Favourite::class, 'favoritable');
    }

    public function AfiliateProduct()
    {
        return $this->hasMany(AfiliateProduct::class);
    }
}
