<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'imag',
    ];


    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }


    public function Favourite_user()
    {
        return $this->morphMany(Favourite::class, 'favoritable');
    }



}
