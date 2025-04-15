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
    public function isActive()
    {
        // تحقق من أن حالة الخصم 'active' (حساس لحالة الأحرف)
        if (strtolower($this->status) !== 'active') {
            return false;
        }

        $now = now();

        // تحقق من أن fromtime ليس في المستقبل
        if ($this->fromtime && $now->lt($this->fromtime)) {
            return false;
        }

        // تحقق من أن totime ليس في الماضي (إذا كان محدداً)
        if ($this->totime && $now->gt($this->totime)) {
            return false;
        }

        return true;
    }
    public function calculateDiscountedPrice($originalPrice)
    {
        if (!$this->isActive()) {
            return $originalPrice;
        }

        $discountAmount = $originalPrice * ($this->value / 100);
        $finalPrice = $originalPrice - $discountAmount;

        // التأكد من أن السعر النهائي ليس أقل من الصفر
        return max($finalPrice, 0);
    }
}
