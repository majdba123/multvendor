<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'sub_category_id',
        'vendor_id',
        'name',
        'discription',
        'price',
    ];


    protected $appends = ['attributes_data'];





    public function subcategory()
    {
        return $this->belongsTo(SubCategory::class,'sub_category_id');
    }
    public function vendor()
    {
        return $this->belongsTo(vendor::class,'vendor_id');
    }
    public function discount()
    {
        return $this->hasOne(Discount::class);
    }
    public function order_product()
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function images()
    {
        return $this->hasMany(ImagProduct::class);
    }


    public function Favourite_user()
    {
        return $this->morphMany(Favourite::class, 'favoritable');
    }

    public function AfiliateProduct()
    {
        return $this->hasMany(AfiliateProduct::class);
    }


    public function scopeByCategory($query, $categoryId)
    {
        return $query->whereHas('subcategory', function ($q) use ($categoryId) {
            $q->where('category_id', $categoryId);
        });
    }

    public function scopeBySubCategory($query, $subCategoryId)
    {
        return $query->where('sub_category_id', $subCategoryId);
    }


    public function ProductAttr()
    {
        return $this->hasMany(ProductAttr::class);
    }


    // ... العلاقات الأخرى الموجودة ...

    public function getAttributesDataAttribute()
    {
        return $this->ProductAttr->map(function($item) {
            return [
                'attribute_id' => $item->attribute_id,
                'product_attributes_id' => $item->id,
                'name_attributes' => $item->Attribute->name,
                'value_attributes' => $item->value
            ];
        });
    }

}
