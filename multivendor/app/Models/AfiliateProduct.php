<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AfiliateProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'afiliate_id',
        'product_id',
        'status',
        'percent',
        'profit',
    ];
    public function Afiliate()
    {
        return $this->belongsTo(Afiliate::class,'afiliate_id');
    }
    public function Product()
    {
        return $this->belongsTo(Product::class,'product_id');
    }
}
