<?php

namespace App\Http\Controllers;
use App\Http\Requests\Vendor\CreateUserAndVendorRequest;
use App\Http\Requests\Vendor\UpdateUserAndVendorRequest;
use Illuminate\Http\JsonResponse;

use App\Services\Vendor\UserVendorService;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    protected $service;

    public function __construct(UserVendorService $service)
    {
        $this->service = $service;
    }



    public function updateUserAndVendor(UpdateUserAndVendorRequest $request, $vendorId): JsonResponse
    {
        // التحقق من البيانات وتأكد من تمرير مصفوفة
        $data = $request->validated();

        // استدعاء الخدمة مع التحقق من نوع البيانات
        $user = $this->service->updateVendorAndUser($vendorId, $data);

        return response()->json([
            'message' => 'vendor updated successfully.',
            'user' => $user,
        ]);
    }

    public function updateVendorStatus(Request $request, $vendorId)
    {
        try {
            // Validate that the `status` field exists and meets the requirements
            $request->validate([
                'status' => 'required|string|in:active,pending,pand|max:255',
            ]);

            // Call the service to update the vendor's status
            $data = $this->service->updateVendorStatus($vendorId, $request->status);

            return response()->json([
                'message' => $data['message'],
                'vendor_id' => $data['vendor_id'],
                'status' => $data['status'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return a custom response for validation errors
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422); // HTTP status code 422 indicates an unprocessable entity
        }
    }


    public function getVendorsByStatus(Request $request)
    {
        // استلام الحالة من الطلب، وإذا لم يتم تحديدها افتراضياً "all"
        $status = $request->input('status', 'all');
        // استدعاء الخدمة للحصول على البيانات
        $vendors = $this->service->getVendorsByStatus($status);

        // إرجاع النتيجة
        return response()->json($vendors);
    }



    public function getVendorInfo($vendorId)
    {
        try {
            // استدعاء الخدمة لجلب معلومات الـ Vendor
            $vendor = $this->service->getVendorInfo($vendorId);

            if (!$vendor) {
                // إذا لم يتم العثور على Vendor
                return response()->json([
                    'message' => 'Vendor not found.',
                ], 404);
            }

            // إرجاع استجابة JSON تحتوي على معلومات الـ Vendor
            return response()->json([
                'message' => 'Vendor information retrieved successfully.',
                'vendor' => $vendor,
            ]);
        } catch (\Exception $e) {
            // التعامل مع أي خطأ غير متوقع
            return response()->json([
                'message' => 'An error occurred while fetching vendor information.',
                'error' => $e->getMessage(),
            ], 500); // HTTP status code 500 يشير إلى خطأ داخلي في الخادم
        }
    }




}
