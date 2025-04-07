<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        User::create([
            'id' => 1,
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'type' => 0,
        ]);

        User::create([
            'id' => 2,
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'type' => "admin",
        ]);

        User::create([
            'id' => 3,
            'name' => 'vendor',
            'email' => 'vendor@example.com',
            'password' => Hash::make('password'),
            'type' => 0,
        ]);

        User::create([
            'id' => 4,
            'name' => 'vendor2',
            'email' => 'vendor2@example.com',
            'password' => Hash::make('password'),
            'type' => 0,
        ]);





    }
}
