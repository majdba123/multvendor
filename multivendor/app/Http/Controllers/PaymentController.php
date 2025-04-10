<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class PaymentController extends Controller
{
    public function handlePaymobReturn(Request $request)
    {
        // في Paymob، يتم إرسال البيانات عبر callback وليس return
        // لذلك يمكن استخدام هذه الصفحة ببساطة لتوجيه المستخدم
        $orderId = $request->input('merchant_order_id');
        $order = Order::find($orderId);

        if (!$order) {
            return $this->handleOrderNotFound($request);
        }

        return $this->handleResponse($request, $order, $order->payment_status);
    }

    public function handlePaymobCallback(Request $request)
    {
        $payload = $request->all();
        
        // التحقق من HMAC signature لأمان الـ Callback
        $hmac = $request->hmac;
        $data = [
            'amount_cents' => $payload['amount_cents'],
            'created_at' => $payload['created_at'],
            'currency' => $payload['currency'],
            'error_occured' => $payload['error_occured'],
            'has_parent_transaction' => $payload['has_parent_transaction'],
            'id' => $payload['id'],
            'integration_id' => $payload['integration_id'],
            'is_3d_secure' => $payload['is_3d_secure'],
            'is_auth' => $payload['is_auth'],
            'is_capture' => $payload['is_capture'],
            'is_refunded' => $payload['is_refunded'],
            'is_standalone_payment' => $payload['is_standalone_payment'],
            'is_voided' => $payload['is_voided'],
            'order' => $payload['order'],
            'owner' => $payload['owner'],
            'pending' => $payload['pending'],
            'source_data_pan' => $payload['source_data']['pan'],
            'source_data_sub_type' => $payload['source_data']['sub_type'],
            'source_data_type' => $payload['source_data']['type'],
            'success' => $payload['success'],
        ];
        
        $calculatedHmac = hash_hmac('sha512', implode('', $data), env('PAYMOB_HMAC_SECRET'));
        
        if ($hmac !== $calculatedHmac) {
            return response()->json(['error' => 'HMAC verification failed'], 403);
        }

        $order = Order::where('transaction_id', $payload['order']['id'])->first();

        if (!$order) {
            return response()->json(['error' => 'الطلب غير موجود'], 404);
        }

        $paymentStatus = $payload['success'] ? 'completed' : 'failed';

        $this->updateOrderStatus($order, $paymentStatus, $payload['id']);

        return response()->json(['success' => true]);
    }

    private function updateOrderStatus(Order $order, string $paymentStatus, ?string $transactionId = null)
    {
        if ($paymentStatus === 'completed') {
            $order->update([
                'status' => 'paid',
                'payment_status' => 'completed',
                'transaction_id' => $transactionId,
            ]);
        } else {
            $order->update([
                'status' => 'failed',
                'payment_status' => 'failed',
            ]);
        }
    }

    private function handleResponse(Request $request, Order $order, string $paymentStatus)
    {
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => $paymentStatus === 'completed',
                'order' => $order,
                'message' => $paymentStatus === 'completed' 
                    ? 'تم الدفع بنجاح! شكرًا لشرائك.' 
                    : 'فشل في عملية الدفع. يرجى المحاولة مرة أخرى.',
            ]);
        }

        return redirect()->route('orders.show', $order->id)->with([
            'payment_status' => $paymentStatus,
            'message' => $paymentStatus === 'completed' 
                ? 'تم الدفع بنجاح! شكرًا لشرائك.' 
                : 'فشل في عملية الدفع. يرجى المحاولة مرة أخرى.',
        ]);
    }

    private function handleOrderNotFound(Request $request)
    {
        if ($request->wantsJson()) {
            return response()->json(['error' => 'الطلب غير موجود'], 404);
        }
        return redirect('/')->with('error', 'الطلب غير موجود!');
    }
}