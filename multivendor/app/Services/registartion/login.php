<?php

namespace App\Services\registartion;

use App\Models\User;
use App\Models\Driver;
use App\Models\Provider_Product; // Import your ProviderProduct model
use App\Models\Provider_Service; // Import your ProviderService model
use Illuminate\Support\Facades\Hash;
class login
{
    /**
     * Register a new user and create related records based on user type.
     *
     * @param array $data
     * @return User
     */
    public function login(array $data)
    {

        if (isset($data['email']) && !isset($data['phone'])) {

            $user = User::where('email', $data['email'])->first();

        } elseif (!isset($data['email']) && isset($data['phone'])) {
            $user = User::where('phone', $data['phone'])->first();

        } else {
            // إذا كانت البيانات تحتوي على البريد الإلكتروني ورقم الهاتف أو لا تحتوي على أي منهما، يمكنك التعامل مع ذلك هنا
            throw new \Exception('يجب أن تحتوي البيانات إما على البريد الإلكتروني أو رقم الهاتف.');
        }



        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid Credentials'
            ], 401);
        }

        $token = $user->createToken($user->name . '-AuthToken')->plainTextToken;

        return $token;
    }

}
