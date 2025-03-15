<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvswerRating extends Model
{
    use HasFactory;
    protected $fillable = [
        'rating_id',
        'user_id',
        'comment',
    ];
    public function rate()
    {
        return $this->belongsTo(Rating::class,'rating_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
}
