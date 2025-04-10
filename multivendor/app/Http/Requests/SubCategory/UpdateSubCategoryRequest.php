<?php

namespace App\Http\Requests\SubCategory;

use App\Models\SubCategory;
use App\Models\Attribute;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubCategoryRequest extends FormRequest
{
    protected $subcategory;
    public function authorize(): bool
    {
        return true; // تغيير إلى false إذا كنت تريد التحقق من الإذن
    }

    public function rules(): array
    {
        $this->subcategory = SubCategory::findOrFail($this->route('id'));
        return [
            'category_id' => 'sometimes|exists:categories,id|string|max:255',
            'name' => 'sometimes|string|max:255', // التحقق من الاسم
            'imag' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'attributes' => 'sometimes|array|min:1',
            'attributes.*.attribute_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    // التحقق أن الخاصية تنتمي لنفس subcategory المنتج
                    $attributeBelongsToProduct = Attribute::where('id', $value)
                        ->where('sub_category_id', $this->subcategory->id)
                        ->exists();

                    if (!$attributeBelongsToProduct) {
                        $fail("The attribute with ID {$value} does not belong to  subcategory.");
                    }
                }
            ],
            'attributes.*.name' => 'required|string|max:255',
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
            'attributes.sometimes' => 'The attributes field is optional but must be an array if provided',
            'attributes.*.attribute_id.required' => 'Attribute ID is required',
            'attributes.*.name.required' => 'Attribute value is required',
            'attributes.*.name.string' => 'Attribute value must be a string',
            'attributes.*.name.max' => 'Attribute value must not exceed 255 characters',
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
