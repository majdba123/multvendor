<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attribute;
use App\Models\SubCategory;

class AttributeSeeder extends Seeder
{
    public function run()
    {
        // مسح البيانات القديمة أولاً (اختياري)
        Attribute::query()->delete();

        // إضافة خصائص مخصصة لكل فئة فرعية
        $this->createAttributesForSubCategory(1, [
            'سعة التخزين' => ['32GB', '64GB', '128GB'],
            'نوع الشاشة' => ['LCD', 'OLED', 'AMOLED'],
            'المعالج' => ['Snapdragon', 'Exynos', 'MediaTek'],
            'عدد الكاميرات' => ['ثنائية', 'ثلاثية', 'رباعية']
        ]);

        $this->createAttributesForSubCategory(2, [
            'نوع المعالج' => ['Core i3', 'Core i5', 'Core i7'],
            'ذاكرة RAM' => ['8GB', '16GB', '32GB'],
            'نظام التشغيل' => ['Windows', 'macOS', 'Linux'],
            'حجم الشاشة' => ['13 بوصة', '15 بوصة', '17 بوصة']
        ]);

        $this->createAttributesForSubCategory(3, [
            'نوع القماش' => ['قطن', 'حرير', 'بوليستر'],
            'المقاس' => ['S', 'M', 'L', 'XL'],
            'النمط' => ['كلاسيكي', 'رياضي', 'عصري'],
            'الموسم' => ['صيفي', 'شتوي', 'ربيعي/خريفي']
        ]);

        $this->createAttributesForSubCategory(4, [
            'نوع الحذاء' => ['رياضي', 'رسمي', 'عرضي'],
            'المقاس' => ['38', '40', '42', '44'],
            'لون الحذاء' => ['أسود', 'أبيض', 'بني'],
            'نوع النعل' => ['مطاط', 'جلد', 'EVA']
        ]);
    }

    /**
     * إنشاء خصائص لفئة فرعية معينة
     *
     * @param int $subCategoryId
     * @param array $attributes
     */
    protected function createAttributesForSubCategory(int $subCategoryId, array $attributes)
    {
        foreach ($attributes as $name => $values) {
            Attribute::create([
                'name' => $name,
                'sub_category_id' => $subCategoryId,
                // يمكنك إضافة حقل للقيم المحتملة إذا كان موجوداً في جدولك
            ]);
        }
    }
}
