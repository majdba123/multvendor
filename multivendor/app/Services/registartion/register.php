<?php

namespace App\Services\registartion;

use App\Models\Afiliate;
use App\Models\User;
use App\Models\Driver;
use App\Models\vendor;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache; // Import Cache facade

class register
{
    /**
     * Register a new user and create related records based on user type.
     *
     * @param array $data
     * @return User
     */
    public function register(array $data): User
    {
        // تحقق من وجود البريد الإلكتروني أو رقم الهاتف
        if (isset($data['email']) && !isset($data['phone'])) {
            // إنشاء المستخدم باستخدام البريد الإلكتروني
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);
        } elseif (!isset($data['email']) && isset($data['phone'])) {
            // إنشاء المستخدم باستخدام رقم الهاتف
            $user = User::create([
                'name' => $data['name'],
                'phone' => $data['phone'],
                'password' => Hash::make($data['password']),
            ]);
        } else {
            // إذا كانت البيانات تحتوي على البريد الإلكتروني ورقم الهاتف أو لا تحتوي على أي منهما، يمكنك التعامل مع ذلك هنا
            throw new \Exception('يجب أن تحتوي البيانات إما على البريد الإلكتروني أو رقم الهاتف.');
        }

        // تحقق من نوع المستخدم وأنشئ السجلات الإضافية إذا لزم الأمر
        switch ($data['type']) {
            case 0:
                // النوع 0: فقط إنشاء مستخدم
                break;
            case 1:
                // النوع 1: إنشاء مستخدم وسجل vendor
                vendor::create([
                    'user_id' => $user->id,
                ]);
                break;
            case 2:
                // النوع 2: إنشاء مستخدم وسجل Afiliate
                Afiliate::create([
                    'user_id' => $user->id,
                    // أضف الحقول الأخرى اللازمة لـ Afiliate
                ]);
                break;
            case 3:
                // النوع 3: إنشاء مستخدم وسجل driver
                // بافتراض أنك لديك موديل Driver
                Driver::create([
                    'user_id' => $user->id,
                    // أضف الحقول الأخرى اللازمة لـ driver
                ]);
                break;
            default:
                throw new \InvalidArgumentException('نوع المستخدم غير صالح');
        }

        return $user;
    }



    public function verifyOtp(string $otp, User $user): bool
    {
        // Retrieve the OTP data from the cache using the authenticated user's ID
        $otpData = Cache::get('otp_' . $user->id);

        // Check if the OTP data exists in the cache
        if (!$otpData) {
            throw new \Exception('No OTP data found in cache.');
        }

        // Retrieve the OTP from the cache data
        $sessionOtp = $otpData['otp'];

        // Check if the OTP matches
        if ($otp !== $sessionOtp) {
            throw new \Exception('Invalid OTP.');
        }

        // If OTP is valid, update the user's otp_verified column
        $user->otp = 1; // Assuming the column name is otp_verified
        $user->save(); // Save the changes to the database

        // Clear the OTP data from the cache after successful verification
        Cache::forget('otp_' . $user->id);

        return true; // Return true if OTP verification is successful
    }
}
