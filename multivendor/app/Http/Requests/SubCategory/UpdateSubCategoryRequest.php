<?php

namespace App\Http\Requests\SubCategory;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // تغيير إلى false إذا كنت تريد التحقق من الإذن
    }

    public function rules(): array
    {
        return [
            'category_id' => 'sometimes|exists:categories,id|string|max:255',
            'name' => 'sometimes|string|max:255', // التحقق من الاسم
            'imag' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',

        ];
    }

    public function messages(): array
    {
        return [
            'category_id.sometimes' => 'The name field is sometimes required.',
            'category_id.exists' => 'The category_id must be a exists.',
            'name.max' => 'The name may not be greater than 255 characters.',
            'name.required' => 'The name field is required.',
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
