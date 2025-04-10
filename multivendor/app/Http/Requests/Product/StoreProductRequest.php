<?php
namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\SubCategory;

class StoreProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'sub_category_id' => 'required|exists:sub_categories,id',
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'attributes' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    $subCategoryId = $this->input('sub_category_id');
                    $requiredAttributes = \App\Models\Attribute::where('sub_category_id', $subCategoryId)
                        ->pluck('id')
                        ->toArray();

                    $submittedAttributes = array_column($value, 'attribute_id');

                    // التحقق من وجود جميع الخصائص المطلوبة
                    $missingAttributes = array_diff($requiredAttributes, $submittedAttributes);

                    if (!empty($missingAttributes)) {
                        $missingAttributesList = implode(', ', $missingAttributes);
                        $fail("يجب تقديم جميع خصائص الفئة الفرعية. الخصائص المفقودة: $missingAttributesList");
                    }

                    // التحقق من عدم وجود خصائص إضافية غير مرتبطة بالـ subcategory
                    $extraAttributes = array_diff($submittedAttributes, $requiredAttributes);
                    if (!empty($extraAttributes)) {
                        $extraAttributesList = implode(', ', $extraAttributes);
                        $fail("الخصائص التالية غير مرتبطة بهذه الفئة الفرعية: $extraAttributesList");
                    }
                }
            ],
            'attributes.*.attribute_id' => [
                'required',
                'exists:attributes,id',
            ],
            'attributes.*.value' => 'required|string|max:255',
        ];
    }
    public function messages()
    {
        return [
            'name.required' => 'The name field is required.',
            'description.required' => 'The description field is required.',
            'price.required' => 'The price field is required.',
            'sub_category_id.required' => 'The sub_category_id field is required.',
            'images.required' => 'At least one image is required.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.mimes' => 'Each image must be of type jpeg, png, jpg, or gif.',
            'images.*.max' => 'Each image must not exceed 2048 kilobytes.',
            'attributes.required' => 'Product attributes are required.',
            'attributes.array' => 'Attributes must be an array.',
            'attributes.min' => 'At least one attribute is required.',
            'attributes.*.attribute_id.required' => 'Each attribute must have an ID.',
            'attributes.*.attribute_id.exists' => 'The selected attribute does not exist.',
            'attributes.*.value.required' => 'Each attribute must have a value.',
            'attributes.*.value.string' => 'Attribute value must be a string.',
            'attributes.*.value.max' => 'Attribute value may not be greater than 255 characters.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors(),
        ], 422));
    }
}
