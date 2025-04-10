<?php
namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\Product;
use App\Models\Attribute;

class UpdateProductRequest extends FormRequest
{
    protected $product;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        // الحصول على المنتج من الرابط باستخدام product_id
        $this->product = Product::findOrFail($this->route('product_id'));

        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'images' => 'sometimes|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'attributes' => 'sometimes|array|min:1',
            'attributes.*.attribute_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    // التحقق أن الخاصية تنتمي لنفس subcategory المنتج
                    $attributeBelongsToProduct = Attribute::where('id', $value)
                        ->where('sub_category_id', $this->product->sub_category_id)
                        ->exists();

                    if (!$attributeBelongsToProduct) {
                        $fail("The attribute with ID {$value} does not belong to this product's subcategory.");
                    }

                    // التحقق أن الخاصية موجودة أصلاً في المنتج
                    $existingAttribute = $this->product->ProductAttr()
                        ->where('attribute_id', $value)
                        ->exists();

                    if (!$existingAttribute) {
                        $fail("Cannot add new attributes. The attribute with ID {$value} is not part of the original product.");
                    }
                }
            ],
            'attributes.*.value' => 'required|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'name.sometimes' => 'The name field is optional but must be a valid string if provided',
            'description.sometimes' => 'The description field is optional but must be a valid string if provided',
            'price.sometimes' => 'The price field is optional but must be a valid number if provided',
            'images.sometimes' => 'The images field is optional but must be an array if provided',
            'images.*.image' => 'Each file must be an image',
            'images.*.mimes' => 'Each image must be of type: jpeg, png, jpg, gif',
            'images.*.max' => 'Each image must not exceed 2048 kilobytes',
            'attributes.sometimes' => 'The attributes field is optional but must be an array if provided',
            'attributes.*.attribute_id.required' => 'Attribute ID is required',
            'attributes.*.value.required' => 'Attribute value is required',
            'attributes.*.value.string' => 'Attribute value must be a string',
            'attributes.*.value.max' => 'Attribute value must not exceed 255 characters',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors(),
        ], 422));
    }

    // دالة مساعدة للحصول على المنتج
    public function getProduct()
    {
        return $this->product;
    }
}
