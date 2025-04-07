<?php

namespace App\Http\Controllers;

use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorController extends Controller
{
    public function getVendorOrders($vendor_id = null)
    {
        if ($vendor_id) {
            // إذا كان $vendor_id موجودًا، فهذا يعني أن الطلب من المسؤول
            $vendor = vendor::findOrFail($vendor_id);
        } else {
            // إذا لم يكن $vendor_id موجودًا، فهذا يعني أن الطلب من التاجر
            $user_id = Auth::user();
            $vendor = vendor::findOrFail($user_id->vendor->id);
        }

        $orders = $vendor->orders()
            ->with(['order:id,status,created_at', 'Product:id,name'])
            ->get();

        return response()->json(['orders' => $orders], 200);
    }



    public function getVendorOrdersByStatus(Request $request)
    {
        // التحقق من أن status موجود في الطلب
        $request->validate([
            'status' => 'required|string|in:pending,complete,cancelled',
        ]);

        $status = $request->status;
        $user_id=Auth::user();
        $vendor=vendor::findOrfail($user_id->vendor->id);
        // جلب الطلبات بناءً على الحالة
        $orders = $vendor->orders()
            ->where('status', $status)
            ->with(['order:id,status,created_at', 'Product:id,name'])
            ->get();

        return response()->json(['orders' => $orders], 200);
    }

    public function getOrdersByProductId($id)
    {
        // جلب المستخدم الحالي
        $user = Auth::user();

        // التحقق من وجود التاجر المرتبط بالمستخدم
        if (!$user || !$user->vendor) {
            return response()->json(['error' => 'Vendor not found for the current user.'], 403);
        }

        // جلب التاجر المرتبط
        $vendor = $user->vendor;

        // البحث عن المنتج بناءً على ID المنتج ومعرف التاجر
        $product = Product::where('id', $id)
            ->where('vendor_id', $vendor->id)
            ->first();

        if (!$product) {
            return response()->json(['error' => 'Product not found or does not belong to this vendor.'], 404);
        }

        // جلب الطلبات المتعلقة بالمنتج
        $orders = $product->order_product()
            ->with(['order:id,status,created_at'])
            ->get();

        return response()->json(['orders' => $orders], 200);
    }



    public function getVendorOrdersByOrderProductStatus(Request $request, $user_id, $product_id = null)
    {
        // التحقق من صحة الإدخال (status)
        $request->validate([
            'status' => 'nullable|string|in:pending,complete,cancelled', // القيم المسموح بها
        ]);

        $status = $request->input('status'); // قراءة حالة الطلب إذا تم إرسالها

        // جلب المستخدم الحالي
        $user = Auth::user();

        // التحقق من وجود التاجر المرتبط بالمستخدم
        if (!$user || !$user->vendor) {
            return response()->json(['error' => 'Vendor not found for the current user.'], 403);
        }

        // جلب التاجر المرتبط
        $vendor = $user->vendor;

        // البحث عن الطلبات الخاصة بـ user_id والتي ترتبط بمنتجات التاجر
        $ordersQuery = OrderProduct::whereHas('order', function ($query) use ($user_id) {
            $query->where('user_id', $user_id); // الطلبات الخاصة بـ user_id
        })->whereHas('Product', function ($query) use ($vendor) {
            $query->where('vendor_id', $vendor->id); // المنتجات الخاصة بالتاجر
        });

        // إذا تم إرسال product_id يتم تصفية الطلبات بناءً عليه
        if ($product_id) {
            $ordersQuery->where('product_id', $product_id);
        }

        // إذا تم إرسال حالة status يتم تصفية الطلبات بناءً عليها
        if ($status) {
            $ordersQuery->where('status', $status);
        }

        // جلب الطلبات مع تضمين العلاقات المطلوبة
        $orders = $ordersQuery
            ->with(['order:id,user_id,status,created_at', 'Product:id,name'])
            ->get();

        // إذا لم يتم العثور على الطلبات
        if ($orders->isEmpty()) {
            return response()->json(['message' => 'No orders found for the specified criteria.'], 404);
        }

        return response()->json(['orders' => $orders], 200);
    }
}
