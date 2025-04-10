<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAttr extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'attribute_id',
        'value',

    ];


    public function product()
    {
        return $this->belongsTo(Product::class,'product_id');
    }

    public function Attribute()
    {
        return $this->belongsTo(Attribute::class,'attribute_id');
    }
}
