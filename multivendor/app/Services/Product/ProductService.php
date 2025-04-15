<?php

namespace App\Services\Product;

use App\Models\Product;
use App\Models\Provider_Product;
use App\Models\Provider_Service;
use App\Models\Rating;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProductService
{
    private function formatProductData($product)
    {
        // تحميل جميع العلاقات المطلوبة بشكل صريح
        $product->loadMissing([
            'subcategory.category',
            'discount',
            'images',
            'ProductAttr.Attribute'
        ]);

        $originalPrice = $product->price;
        $discountData = ['is_discount_active' => false];
        $finalPrice = $originalPrice;

        // إذا كان هناك خصم مرتبط بالمنتج
        if ($product->discount) {
            $discount = $product->discount;
            $isActive = $discount->isActive();

            $discountData = [
                'discount_id' => $discount->id,
                'discount_value' => $discount->value,
                'discount_percentage' => $discount->value . '%',
                'discount_from' => $discount->fromtime,
                'discount_to' => $discount->totime,
                'is_discount_active' => $isActive,
            ];

            if ($isActive) {
                $finalPrice = $discount->calculateDiscountedPrice($originalPrice);
                $finalPrice = number_format($finalPrice, 2, '.', '');
            }
        }

        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'original_price' => number_format($originalPrice, 2, '.', ''),
            'final_price' => $finalPrice,
            'subcategory' => $product->subcategory->name ?? null,
            'subcategory_id' => $product->subcategory->id ?? null,
            'category' => $product->subcategory->category->name ?? null,
            'category_id' => $product->subcategory->category->id ?? null,
            'discount_info' => $discountData,
            'images' => $product->images->pluck('imag'),
            'attributes' => $product->attributes_data
        ];
    }

    public function createProduct(array $data, $vendor_id)
    {
        $product = Product::create([
            'name' => $data['name'],
            'description' => $data['description'],
            'price' => $data['price'],
            'sub_category_id' => $data['sub_category_id'],
            'vendor_id' => $vendor_id,
        ]);

        if (isset($data['attributes']) && is_array($data['attributes'])) {
            foreach ($data['attributes'] as $attribute) {
                $product->ProductAttr()->create([
                    'attribute_id' => $attribute['attribute_id'],
                    'value' => $attribute['value'],
                ]);
            }
        }

        $productWithRelations = Product::with(['subcategory.Category', 'discount', 'images', 'ProductAttr.Attribute'])
            ->find($product->id);

        return $this->formatProductData($productWithRelations);
    }

    public function updateProduct(array $data, $product)
    {
        $product->update([
            'name' => $data['name'] ?? $product->name,
            'description' => $data['description'] ?? $product->description,
            'price' => $data['price'] ?? $product->price,
            'sub_category_id' => $data['sub_category_id'] ?? $product->sub_category_id,
        ]);

        if (isset($data['attributes']) && is_array($data['attributes'])) {
            foreach ($data['attributes'] as $attributeData) {
                $existingAttribute = $product->ProductAttr()
                    ->where('attribute_id', $attributeData['attribute_id'])
                    ->first();

                if ($existingAttribute) {
                    $existingAttribute->update(['value' => $attributeData['value']]);
                }
            }
        }

        $updatedProduct = Product::with(['subcategory.Category', 'discount', 'images', 'ProductAttr.Attribute'])
            ->find($product->id);

        return $this->formatProductData($updatedProduct);
    }

    public function deleteProduct($id): array
    {
        $product = Product::find($id);
        $product->delete();

        return ['message' => 'Product deleted successfully', 'status' => 200];
    }

    public function getLatestProducts($perPage)
    {
        return Product::with(['subcategory.Category', 'discount', 'images', 'ProductAttr.Attribute'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(function ($product) {
                return $this->formatProductData($product);
            });
    }

    public function getProductById($id)
    {
        $product = Product::with(['subcategory.Category', 'discount', 'images', 'ProductAttr.Attribute'])->find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return $this->formatProductData($product);
    }

    public function getPaginatedVendorProducts($vendorId, $perPage, $name = null, $category = null, $subcategory = null, $minPrice = 0, $maxPrice = PHP_INT_MAX)
    {
        $query = Product::where('vendor_id', $vendorId);

        if (!is_null($name) && !empty($name)) {
            $query->where('name', 'LIKE', "%$name%");
        }

        if (!is_null($category)) {
            $query->whereHas('subcategory.Category', function ($q) use ($category) {
                $q->where('id', $category);
            });
        }

        if (!is_null($subcategory)) {
            $query->where('sub_category_id', $subcategory);
        }

        if ($minPrice >= 0 && $maxPrice >= $minPrice) {
            $query->whereBetween('price', [$minPrice, $maxPrice]);
        }

        return $query->with(['subcategory.Category', 'discount', 'images', 'ProductAttr.Attribute'])
            ->paginate($perPage)
            ->through(function ($product) {
                return $this->formatProductData($product);
            });
    }

    public function getProductsByCategory($categoryId, $perPage)
    {
        return Product::whereHas('subcategory', function ($query) use ($categoryId) {
            $query->where('category_id', $categoryId);
        })
        ->with(['subcategory.Category', 'discount', 'images', 'ProductAttr.Attribute'])
        ->orderBy('created_at', 'desc')
        ->paginate($perPage)
        ->through(function ($product) {
            return $this->formatProductData($product);
        });
    }

    public function getProductsBySubCategory($subCategoryId, $perPage)
    {
        return Product::where('sub_category_id', $subCategoryId)
            ->with(['subcategory.Category', 'discount', 'images', 'ProductAttr.Attribute'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(function ($product) {
                return $this->formatProductData($product);
            });
    }

    public function searchProducts($name = null, $minPrice = 0, $maxPrice = PHP_INT_MAX, $perPage = 5)
    {
        $query = Product::query();

        if (!is_null($name) && !empty($name)) {
            $query->where('name', 'LIKE', "%$name%");
        }

        if ($minPrice >= 0 && $maxPrice >= $minPrice) {
            $query->whereBetween('price', [$minPrice, $maxPrice]);
        }

        return $query->with(['subcategory.Category', 'discount', 'images', 'ProductAttr.Attribute'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(function ($product) {
                return $this->formatProductData($product);
            });
    }

    public function getProductsByVendor($vendorId, $perPage)
    {
        return Product::where('vendor_id', $vendorId)
            ->with(['subcategory.Category', 'discount', 'images', 'ProductAttr.Attribute'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(function ($product) {
                return $this->formatProductData($product);
            });
    }
}
