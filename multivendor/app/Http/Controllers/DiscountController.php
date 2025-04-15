<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Discount\DiscountStoreRequest;
use App\Http\Requests\Discount\DiscountUpdateRequest;
use Illuminate\Support\Facades\Auth;

class DiscountController extends Controller
{
    /**
     * إنشاء خصم جديد للمنتج
     */
    public function store(DiscountStoreRequest $request, $product_id)
    {
        $product = Product::findOrFail($product_id);
        $use=Auth::user();
        // التحقق من أن المستخدم هو التاجر صاحب المنتج
        if ($use->vendor->id !== $product->vendor_id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بإنشاء خصم لهذا المنتج',
                'errors' => ['authorization' => ['يمكن فقط للتاجر صاحب المنتج إنشاء خصم']]
            ], 403);
        }

        // التحقق من عدم وجود خصم فعال للمنتج
        $existingActiveDiscount = Discount::where('product_id', $product->id)
            ->where('status', 'active')
            ->exists();

        if ($existingActiveDiscount) {
            return response()->json([
                'success' => false,
                'message' => 'يوجد بالفعل خصم فعال لهذا المنتج',
                'errors' => ['product_id' => ['لا يمكن إنشاء خصم جديد لمنتج لديه خصم فعال']]
            ], 422);
        }

        $discount = DB::transaction(function () use ($product, $request) {
            return Discount::create([
                'product_id' => $product->id,
                'vendor_id' => $product->vendor_id,
                'value' => $request->value,
                'fromtime' => $request->from_time,
                'totime' => $request->to_time,
                'status' => 'active',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء الخصم بنجاح',
            'data' => $discount
        ], 201);
    }

    /**
     * تحديث خصم موجود
     */
    public function update(DiscountUpdateRequest $request, $product_id)
    {
        $product = Product::findOrFail($product_id);
        $use=Auth::user();
        // التحقق من أن المستخدم هو التاجر صاحب المنتج
        if ($use->vendor->id !== $product->vendor_id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتحديث خصم لهذا المنتج',
                'errors' => ['authorization' => ['يمكن فقط للتاجر صاحب المنتج تحديث الخصم']]
            ], 403);
        }

        if (!$product->discount) {
            return response()->json([
                'success' => false,
                'message' => 'لا يوجد خصم لهذا المنتج',
                'errors' => ['product_id' => ['لم يتم العثور على خصم لهذا المنتج']]
            ], 404);
        }

        // إذا كان التحديث يتضمن تفعيل الخصم، نتحقق من عدم وجود خصم فعال آخر
        if ($request->has('status') && $request->status === 'active') {
            $existingActiveDiscount = Discount::where('product_id', $product->id)
                ->where('id', '!=', $product->discount->id)
                ->where('status', 'active')
                ->exists();

            if ($existingActiveDiscount) {
                return response()->json([
                    'success' => false,
                    'message' => 'يوجد بالفعل خصم فعال آخر لهذا المنتج',
                    'errors' => ['product_id' => ['لا يمكن تفعيل أكثر من خصم لنفس المنتج']]
                ], 422);
            }
        }

        $discount = DB::transaction(function () use ($product, $request) {
            $updateData = [];

            if ($request->has('value')) {
                $updateData['value'] = $request->value;
            }

            if ($request->has('from_time')) {
                $updateData['fromtime'] = $request->from_time;
            }

            if ($request->has('to_time')) {
                $updateData['totime'] = $request->to_time;
            }

            if ($request->has('status')) {
                $updateData['status'] = $request->status;
            }

            $product->discount()->update($updateData);

            return $product->discount->fresh();
        });

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الخصم بنجاح',
            'data' => $discount
        ], 200);
    }
    /**
     * تغيير حالة الخصم (تفعيل/تعطيل)
     */
    public function changeStatus(Request $request, $product_id)
    {
        $request->validate([
            'status' => 'required|in:active,inactive'
        ], [
            'status.required' => 'حقل حالة الخصم مطلوب',
            'status.in' => 'حالة الخصم يجب أن تكون إما active أو inactive',
        ]);

        $product = Product::findOrFail($product_id);

        $use=Auth::user();
        // التحقق من أن المستخدم هو التاجر صاحب المنتج
        if ($use->vendor->id !== $product->vendor_id) {
            return response()->json([
                'message' => 'غير مصرح لك بتغيير حالة خصم لهذا المنتج',
                'errors' => ['authorization' => ['يمكن فقط للتاجر صاحب المنتج تغيير حالة الخصم']]
            ], 403);
        }

        if (!$product->discount) {
            return response()->json([
                'message' => 'لا يوجد خصم لهذا المنتج',
                'errors' => ['product_id' => ['لم يتم العثور على خصم لهذا المنتج']]
            ], 404);
        }

        // إذا كان التفعيل، نتحقق من عدم وجود خصم فعال آخر
        if ($request->status === 'active') {
            $existingActiveDiscount = Discount::where('product_id', $product->id)
                ->where('id', '!=', $product->discount->id)
                ->where('status', 'active')
                ->exists();

            if ($existingActiveDiscount) {
                return response()->json([
                    'message' => 'يوجد بالفعل خصم فعال لهذا المنتج',
                    'errors' => ['product_id' => ['لا يمكن تفعيل أكثر من خصم لنفس المنتج']]
                ], 422);
            }
        }

        $product->discount()->update(['status' => $request->status]);

        return response()->json([
            'message' => $request->status === 'active'
                ? 'تم تفعيل الخصم بنجاح'
                : 'تم تعطيل الخصم بنجاح',
            'discount' => $product->discount->fresh()
        ]);
    }

    /**
     * حذف الخصم
     */
    public function destroy($product_id)
    {
        $product = Product::findOrFail($product_id);

        // التحقق من وجود الخصم
        if (!$product->discount) {
            return response()->json([
                'message' => 'لا يوجد خصم لهذا المنتج',
                'errors' => ['product_id' => ['لم يتم العثور على خصم لهذا المنتج']]
            ], 404);
        }

        $use=Auth::user();
        // التحقق من أن المستخدم هو التاجر صاحب المنتج
        if ($use->vendor->id !== $product->vendor_id) {
            return response()->json([
                'message' => 'غير مصرح لك بحذف هذا الخصم',
                'errors' => ['authorization' => ['يمكن فقط للتاجر صاحب المنتج حذف الخصم']]
            ], 403);
        }

        // تنفيذ الحذف
        $product->discount()->delete();

        return response()->json([
            'message' => 'تم حذف الخصم بنجاح'
        ]);
    }
}
