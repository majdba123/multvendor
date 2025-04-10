<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class CreateOrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'products' => 'required|array',
            'products.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'products.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|string|in:visa,paymob', // تحقق من طريقة الدفع

        ];
    }

    public function messages()
    {
        return [
            'products.required' => 'The products field is required.',
            'products.*.product_id.required' => 'Each product must have a product ID.',
            'products.*.product_id.integer' => 'The product ID must be an integer.',
            'products.*.product_id.exists' => 'The selected product does not exist.',
            'products.*.quantity.required' => 'Each product must have a quantity.',
            'products.*.quantity.integer' => 'The quantity must be an integer.',
            'products.*.quantity.min' => 'The quantity must be at least 1.',
            'payment_method.required' => 'The payment method field is required.',
            'payment_method.in' => 'The selected payment method is invalid.',
            'products.required' => 'The products field is required.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        // تخصيص رسالة الخطأ
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors(),
        ], 422));
    }
}
