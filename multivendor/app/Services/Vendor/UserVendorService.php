<?php

namespace App\Services\Vendor;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\Hash;

class UserVendorService
{



    public function getVendorInfo($vendorId)
    {
        $vendor =Vendor::findOrfail($vendorId);


        return [
            'user_name' => $vendor->user->name,
            'user_email' => $vendor->user->email,
            'user_id' => $vendor->user->id,
            'vendor_id' => $vendor->user->id,
            'vendor_status' => $vendor->status,
        ];
    }




    public function updateVendorAndUser($vendorId, array $data)
    {
            // استدعاء الـ Vendor
            $vendor = Vendor::findOrFail($vendorId);
            // استدعاء المستخدم المرتبط بالـ Vendor
            $user = $vendor->user;
            // تحديث بيانات المستخدم
            if (isset($data['name'])) {
                $user->name = $data['name'];
            }
            if (isset($data['email'])) {
                $user->email = $data['email'];
            }
            if (isset($data['password'])) {
                $user->password = Hash::make($data['password']);
            }
            // حفظ التعديلات في سجل المستخدم
            $user->save();
              // إرجاع بيانات مخصصة
            return [
                'user_id' => $user->id,
                'vendor_id' => $vendor->id,
                'status' => $vendor->status,
                'name' => $user->name,
                'email' => $user->email,
                'password' => $data['password'] ?? 'Password not updated', // إرسال كلمة المرور الخام فقط إذا تم إرسالها
            ];
    }



    public function updateVendorStatus($vendorId, $status)
    {
        // استدعاء الـ Vendor باستخدام الـ ID
        $vendor = Vendor::findOrFail($vendorId);

        // تحديث الحالة
        $vendor->status = $status;

        // حفظ التعديلات
        $vendor->save();

        // إرجاع البيانات
        return [
            'vendor_id' => $vendor->id,
            'status' => $vendor->status,
            'message' => 'Vendor status updated successfully.',
        ];
    }


    public function getVendorsByStatus($status, $perPage = 5)
    {
        $query = $status === 'all' ? Vendor::query() : Vendor::where('status', $status);

        // استرداد النتائج مع الصفحة المحددة
        $vendors = $query->paginate($perPage);

        // تخصيص البيانات المرجعة
        return [
            'data' => $vendors->map(function ($vendor) {
                $user = $vendor->user; // افترض وجود علاقة بين Vendor و User
                return [
                    'user_id' => $user->id ?? null,
                    'vendor_id' => $vendor->id,
                    'status' => $vendor->status,
                    'name' => $user->name ?? null,
                    'email' => $user->email ?? null,
                ];
            }),
            'pagination' => [
                'current_page' => $vendors->currentPage(),
                'last_page' => $vendors->lastPage(),
                'per_page' => $vendors->perPage(),
                'total' => $vendors->total(),
            ],
        ];
    }



}
