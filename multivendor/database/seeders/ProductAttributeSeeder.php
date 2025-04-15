<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Attribute;
use App\Models\ProductAttr;

class ProductAttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // مسح البيانات القديمة أولاً (اختياري)
        ProductAttr::query()->delete();

        // الحصول على جميع المنتجات
        $products = Product::all();

        foreach ($products as $product) {
            // الحصول على خصائص الفئة الفرعية لهذا المنتج
            $attributes = Attribute::where('sub_category_id', $product->sub_category_id)->get();

            foreach ($attributes as $attribute) {
                // إنشاء قيمة عشوائية للخاصية بناءً على نوعها
                $value = $this->getRandomValueForAttribute($attribute);

                ProductAttr::create([
                    'product_id' => $product->id,
                    'attribute_id' => $attribute->id,
                    'value' => $value,
                ]);
            }
        }
    }

    /**
     * إنشاء قيمة عشوائية للخاصية بناءً على نوعها
     *
     * @param \App\Models\Attribute $attribute
     * @return string
     */
    protected function getRandomValueForAttribute($attribute)
    {
        // يمكنك استبدال هذا ببياناتك الفعلية أو منطق أكثر تعقيداً
        $values = [
            'سعة التخزين' => ['32GB', '64GB', '128GB'],
            'نوع الشاشة' => ['LCD', 'OLED', 'AMOLED'],
            'المعالج' => ['Snapdragon', 'Exynos', 'MediaTek'],
            'عدد الكاميرات' => ['ثنائية', 'ثلاثية', 'رباعية'],
            'نوع المعالج' => ['Core i3', 'Core i5', 'Core i7'],
            'ذاكرة RAM' => ['8GB', '16GB', '32GB'],
            'نظام التشغيل' => ['Windows', 'macOS', 'Linux'],
            'حجم الشاشة' => ['13 بوصة', '15 بوصة', '17 بوصة'],
            'نوع القماش' => ['قطن', 'حرير', 'بوليستر'],
            'المقاس' => ['S', 'M', 'L', 'XL'],
            'النمط' => ['كلاسيكي', 'رياضي', 'عصري'],
            'الموسم' => ['صيفي', 'شتوي', 'ربيعي/خريفي'],
            'نوع الحذاء' => ['رياضي', 'رسمي', 'عرضي'],
            'لون الحذاء' => ['أسود', 'أبيض', 'بني'],
            'نوع النعل' => ['مطاط', 'جلد', 'EVA']
        ];

        return $values[$attribute->name][array_rand($values[$attribute->name])];
    }
}
