<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'vendor_id',
        'status',
        'fromtime',
        'totime',
        'value',

    ];
    public function product()
    {
        return $this->belongsTo(Product::class,'product_id');
    }

    public function vendor()
    {
        return $this->belongsTo(vendor::class,'user_id');
    }
}
