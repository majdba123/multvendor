<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImagProduct extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'imag',
    ];
    public function product()
    {
        return $this->belongsTo(Product::class,'product_id');
    }
    public function sub_category()
    {
        return $this->hasMany(SubCategory::class);
    }
}
