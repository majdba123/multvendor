<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Product::create([
            'sub_category_id' => 1,
            'vendor_id' => 1,
            'name' => 'Product 1',
            'discription' => 'This is a description for Product 1.',
            'price' => 100.00,
        ]);

        Product::create([
            'sub_category_id' => 1,
            'vendor_id' => 1,
            'name' => 'Product 2',
            'discription' => 'This is a description for Product 2.',
            'price' => 100.00,
        ]);

        Product::create([
            'sub_category_id' => 2,
            'vendor_id' => 2,
            'name' => 'Product 3',
            'discription' => 'This is a description for Product 2.',
            'price' => 150.00,
        ]);
        Product::create([
            'sub_category_id' => 2,
            'vendor_id' => 2,
            'name' => 'Product 3',
            'discription' => 'This is a description for Product 2.',
            'price' => 150.00,
        ]);

        // أضف المزيد من البيانات حسب الحاجة
    }
}
