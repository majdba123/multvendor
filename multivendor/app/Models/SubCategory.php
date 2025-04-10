<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    use HasFactory;
    protected $fillable = [
        'category_id',
        'name',
        'imag',

    ];
    public function Category()
    {
        return $this->belongsTo(Category::class,'category_id');
    }
    public function product()
    {
        return $this->hasMany(Product::class);
    }
    public function attribute()
    {
        return $this->hasMany(Attribute::class);
    }
}
