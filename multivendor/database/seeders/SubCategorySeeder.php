<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubCategory;

class SubCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SubCategory::create([
            'category_id' => 1,
            'name' => 'SubCategory 1',
        ]);

        SubCategory::create([
            'category_id' => 1,
            'name' => 'SubCategory 2',
        ]);

        SubCategory::create([
            'category_id' => 2,
            'name' => 'SubCategory 1',
        ]);

        SubCategory::create([
            'category_id' => 2,
            'name' => 'SubCategory 2',
        ]);

        // أضف المزيد من البيانات حسب الحاجة
    }
}
