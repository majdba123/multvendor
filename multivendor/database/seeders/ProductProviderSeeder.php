<?php

namespace Database\Seeders;

use App\Models\Provider_Product;
use App\Models\vendor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        vendor::create([
            'user_id' => 3,
        ]);

        vendor::create([
            'user_id' => 4,
        ]);


    }
}
