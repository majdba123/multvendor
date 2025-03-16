<?php

namespace App\Http\Requests\SubCategory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreSubCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // تغيير إلى false إذا كنت تريد التحقق من الإذن
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id', // التحقق من وجود الفئة
            'name' => 'required|string|max:255', // التحقق من الاسم
            'imag' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',

        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'The category_id field is required.',
            'category_id.exists' => 'The category_id must be a string.',
            'name.max' => 'The name may not be greater than 255 characters.',
            'name.required' => 'The name field is required.',
            'imag.required' => 'The image field is required.',
            'imag.image' => 'The uploaded file must be an image.',
            'imag.mimes' => 'The image must be a file of type: jpeg, png, jpg, gif, svg.',
            'imag.max' => 'The image must not be greater than 2048 kilobytes.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        // Customize the response for validation errors
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors(),
        ], 422));
    }


    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            if ($this->input('type') == 0 && $this->input('price') > 100) {
                $validator->errors()->add('price', 'The price for a product type category may not be greater than 100.');
            }
        });
    }
}
