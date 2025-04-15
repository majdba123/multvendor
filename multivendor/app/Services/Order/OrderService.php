<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Models\Product;
use App\Models\OrderProduct;
use Illuminate\Support\Facades\Auth;
use App\Models\vendor;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\Charge;
use Illuminate\Support\Facades\Http; // Add this import

class OrderService
{
    public function createOrder(array $validatedData)
    {
        // بدء transaction
        DB::beginTransaction();

        try {
            $userId = Auth::id();

            // إنشاء الطلب
            $order = Order::create([
                'user_id' => $userId,
                'total_price' => 0,
                'status' => 'pending',
                'payment_method' => $validatedData['payment_method'],
                'payment_status' => 'pending',
            ]);

            // حساب السعر وإضافة المنتجات
            $totalPrice = 0;
            $orderProductsDetails = [];

            foreach ($validatedData['products'] as $productData) {
                $product = Product::with('discount')->find($productData['product_id']);

                if (!$product) {
                    throw new \Exception('المنتج غير موجود: ' . $productData['product_id']);
                }

                // حساب السعر مع تطبيق الخصم إذا كان موجوداً وفعالاً
                $originalPrice = $product->price;
                $discountApplied = false;
                $discountValue = 0;
                $productPrice = $originalPrice;

                if ($product->discount && $product->discount->isActive()) {
                    $discountApplied = true;
                    $discountValue = $product->discount->value;
                    $productPrice = $product->discount->calculateDiscountedPrice($originalPrice);
                }

                $orderProduct = OrderProduct::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $productData['quantity'],
                    'total_price' => $productPrice * $productData['quantity'],
                    'status' => 'pending',
                ]);

                $totalPrice += $orderProduct->total_price;

                // إضافة تفاصيل المنتج للرد
                $orderProductsDetails[] = [
                    'id' => $orderProduct->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $productData['quantity'],
                    'original_unit_price' => $originalPrice,
                    'final_unit_price' => $productPrice,
                    'discount_applied' => $discountApplied,
                    'discount_value' => $discountApplied ? $product->discount->value : 0,
                    'total_price' => $productPrice * $productData['quantity'],
                ];
            }

            $order->update(['total_price' => $totalPrice]);

            // تحضير البيانات للرد
            $responseData = [
                'success' => true,
                'order' => [
                    'id' => $order->id,
                    'user_id' => $order->user_id,
                    'total_price' => $order->total_price,
                    'status' => $order->status,
                    'payment_method' => $order->payment_method,
                    'payment_status' => $order->payment_status,
                    'created_at' => $order->created_at,
                    'products' => $orderProductsDetails,
                ],
                'message' => 'تم إنشاء الطلب بنجاح',
            ];

            // إذا كان الدفع عبر Paymob
            if ($validatedData['payment_method'] === 'paymob') {
                try {
                    $paymentResponse = $this->createPaymobPayment($order);

                    $responseData['payment_url'] = $paymentResponse['payment_url'];
                    $responseData['message'] = 'يجب توجيه المستخدم إلى رابط الدفع لإتمام العملية';

                    // تأكيد العملية إذا نجحت كل شيء
                    DB::commit();

                    return response()->json($responseData);

                } catch (\Exception $e) {
                    // التراجع عن كل التغييرات في حالة فشل Paymob
                    DB::rollBack();

                    return response()->json([
                        'success' => false,
                        'message' => 'فشل في إنشاء طلب الدفع: ' . $e->getMessage(),
                    ], 500);
                }
            }

            // إذا كان كل شيء ناجحاً ولم يكن هناك مشاكل
            DB::commit();

            // إذا كان الدفع نقداً أو بأي طريقة أخرى غير Paymob
            return response()->json($responseData);

        } catch (\Exception $e) {
            // التراجع عن كل التغييرات في حالة حدوث أي خطأ
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'فشل في إنشاء الطلب: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function createPaymobPayment(Order $order)
    {
        $apiKey = env('PAYMOB_API_KEY');
        $integrationId = env('PAYMOB_INTEGRATION_ID');
        $iframeId = env('PAYMOB_IFRAME_ID');

        // الخطوة 1: الحصول على token authentication
        $authResponse = Http::post('https://accept.paymob.com/api/auth/tokens', [
            'api_key' => $apiKey
        ]);

        if (!$authResponse->successful()) {
            throw new \Exception('فشل في الحصول على token من Paymob');
        }

        $token = $authResponse->json('token');

        // الخطوة 2: إنشاء طلب الدفع
        $orderResponse = Http::post('https://accept.paymob.com/api/ecommerce/orders', [
            'auth_token' => $token,
            'delivery_needed' => 'false',
            'amount_cents' => $order->total_price * 100, // Paymob يعمل بالسنتات
            'currency' => 'EGP',
            'items' => [],
        ]);

        if (!$orderResponse->successful()) {
            throw new \Exception('فشل في إنشاء طلب الدفع في Paymob');
        }

        $paymobOrderId = $orderResponse->json('id');

        // الخطوة 3: إنشاء مفتاح دفع
        $paymentKeyResponse = Http::post('https://accept.paymob.com/api/acceptance/payment_keys', [
            'auth_token' => $token,
            'amount_cents' => $order->total_price * 100,
            'expiration' => 3600, // صلاحية الرابط بالثواني
            'order_id' => $paymobOrderId,
            'billing_data' => [
                'apartment' => 'NA',
                'email' => $order->user->email,
                'floor' => 'NA',
                'first_name' => $order->user->name,
                'street' => 'NA',
                'building' => 'NA',
                'phone_number' => $order->user->phone ?? 'NA',
                'shipping_method' => 'NA',
                'postal_code' => 'NA',
                'city' => 'NA',
                'country' => 'NA',
                'last_name' => 'NA',
                'state' => 'NA'
            ],
            'currency' => 'EGP',
            'integration_id' => $integrationId
        ]);

        if (!$paymentKeyResponse->successful()) {
            throw new \Exception('فشل في إنشاء مفتاح الدفع في Paymob');
        }

        $paymentKey = $paymentKeyResponse->json('token');

        // حفظ معرف طلب Paymob في الطلب الخاص بنا
        $order->update(['transaction_id' => $paymobOrderId]);

        // إنشاء رابط الدفع
        $paymentUrl = "https://accept.paymob.com/api/acceptance/iframes/{$iframeId}?payment_token={$paymentKey}";

        return [
            'payment_url' => $paymentUrl,
            'paymob_order_id' => $paymobOrderId
        ];
    }















































































































    public function getOrdersByPriceRange($minPrice, $maxPrice)
    {
        $orders = Order::whereBetween('total_price', [$minPrice, $maxPrice])
            ->with(['order_product:id,order_id,product_id', 'order_product.Product:id,name'])
            ->paginate(8); // تحديد عدد الطلبات في كل صفحة (10 طلبات)

        return response()->json(['orders' => $orders], 200);
    }

    public function getAllOrders()
    {
        return Order::with(['order_product:id,order_id,product_id', 'order_product.Product:id,name'])
            ->paginate(8); // تقسيم الطلبات إلى صفحات
    }



    public function getOrdersByStatus($status)
    {
        if ($status === 'all') {
            return $this->getAllOrders(); // استدعاء الدالة التي تسترجع جميع الطلبات
        }

        return Order::where('status', $status)
            ->with(['order_product:id,order_id,product_id', 'order_product.Product:id,name'])
            ->paginate(8); // تقسيم الطلبات إلى صفحات
    }




    public function getOrdersByProduct($productId)
    {
        $orders = OrderProduct::where('product_id', $productId)
            ->with(['order:id,status,total_price,user_id', 'order.user:id,name,email'])
            ->paginate(8); // تحديد عدد الطلبات في كل صفحة (10 طلبات)

        return $orders;
    }

    public function getOrdersByUser($userId)
    {
        $orders = Order::where('user_id', $userId)
            ->with(['order_product:id,order_id,product_id,status,total_price', 'order_product.product:id,name,price'])
            ->paginate(8); // تحديد عدد الطلبات في كل صفحة (10 طلبات)

        return $orders;
    }

    public function getOrdersByCategory($categoryId)
    {
        $products = Product::byCategory($categoryId)->pluck('id');

        $orders = OrderProduct::whereIn('product_id', $products)
            ->with(['order:id,status,user_id,total_price', 'order.user:id,name,email'])
            ->paginate(8); // تحديد عدد الطلبات في كل صفحة (10 طلبات)

        return $orders;
    }


    public function getOrdersBySubCategory($subCategoryId)
    {
        $products = Product::bySubCategory($subCategoryId)->pluck('id');

        $orders = OrderProduct::whereIn('product_id', $products)
            ->with(['order:id,status,user_id,total_price', 'order.user:id,name,email'])
            ->paginate(8); // تحديد عدد الطلبات في كل صفحة (10 طلبات)

        return $orders;
    }







}
