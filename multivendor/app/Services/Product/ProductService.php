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
    public function createProduct(array $data, $vendor_id)
    {
        // إنشاء المنتج الأساسي
        $product = Product::create([
            'name' => $data['name'],
            'description' => $data['description'],
            'price' => $data['price'],
            'sub_category_id' => $data['sub_category_id'],
            'vendor_id' => $vendor_id,
        ]);

        // إضافة الخصائص (Attributes) للمنتج
        if (isset($data['attributes']) && is_array($data['attributes'])) {
            foreach ($data['attributes'] as $attribute) {
                $product->ProductAttr()->create([
                    'attribute_id' => $attribute['attribute_id'],
                    'value' => $attribute['value'],
                ]);
            }
        }

        // جلب المنتج مع العلاقات لإرجاعه بنفس هيكل باقي الدوال
        $productWithRelations = Product::with(['subcategory.Category', 'discount', 'images', 'ProductAttr.Attribute'])
            ->find($product->id);

        return [
            'id' => $productWithRelations->id,
            'name' => $productWithRelations->name,
            'description' => $productWithRelations->description,
            'price' => $productWithRelations->price,
            'subcategory' => $productWithRelations->subcategory->name ?? null,
            'subcategory_id' => $productWithRelations->subcategory->id ?? null,
            'category' => $productWithRelations->subcategory->category->name ?? null,
            'category_id' => $productWithRelations->subcategory->category->id ?? null,
            'discount' => $productWithRelations->discount->percentage ?? 0,
            'images' => $productWithRelations->images->pluck('imag'),
            'attributes' => $productWithRelations->attributes_data
        ];
    }

    public function updateProduct(array $data, $product)
    {
        // تحديث بيانات المنتج الأساسية
        $product->update([
            'name' => $data['name'] ?? $product->name,
            'description' => $data['description'] ?? $product->description,
            'price' => $data['price'] ?? $product->price,
            'sub_category_id' => $data['sub_category_id'] ?? $product->sub_category_id,
        ]);

        // تحديث الخصائص (Attributes) للمنتج
        if (isset($data['attributes']) && is_array($data['attributes'])) {
            foreach ($data['attributes'] as $attributeData) {
                $existingAttribute = $product->ProductAttr()
                    ->where('attribute_id', $attributeData['attribute_id'])
                    ->first();

                if ($existingAttribute) {
                    $existingAttribute->update(['value' => $attributeData['value']]);
                } else {
                    $product->ProductAttr()->create([
                        'attribute_id' => $attributeData['attribute_id'],
                        'value' => $attributeData['value']
                    ]);
                }
            }
        }

        // جلب المنتج مع العلاقات بعد التحديث
        $updatedProduct = Product::with(['subcategory.Category', 'discount', 'images', 'ProductAttr.Attribute'])
            ->find($product->id);

        return [
            'id' => $updatedProduct->id,
            'name' => $updatedProduct->name,
            'description' => $updatedProduct->description,
            'price' => $updatedProduct->price,
            'subcategory' => $updatedProduct->subcategory->name ?? null,
            'subcategory_id' => $updatedProduct->subcategory->id ?? null,
            'category' => $updatedProduct->subcategory->category->name ?? null,
            'category_id' => $updatedProduct->subcategory->category->id ?? null,
            'discount' => $updatedProduct->discount->percentage ?? 0,
            'images' => $updatedProduct->images->pluck('imag'),
            'attributes' => $updatedProduct->attributes_data
        ];
    }

    public function deleteProduct($id): array
    {
        $product = Product::find($id);

        // تنفيذ عملية الحذف باستخدام الـ "Soft Delete"
        $product->delete();

        return ['message' => 'Product deleted successfully', 'status' => 200];
    }











    public function getProductById($id)
    {
        $product = Product::with(['subcategory.Category', 'discount', 'images', 'ProductAttr.Attribute'])->find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'subcategory' => $product->subcategory->name ?? null,
            'subcategory_id' => $product->subcategory->id ?? null,
            'images' => $product->images->pluck('imag'),
            'attributes' => $product->attributes_data // استخدام الخاصية المضافة
        ];
    }


  /*  public function getProductRatings($productId)
    {
        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        // جلب التقييمات بناءً على معرّف المنتج
        $ratings = Rating::where('product_id', $productId)->get();

        if ($ratings->isEmpty()) {
            return response()->json(['message' => 'No ratings found for this product'], 404);
        }

        return $ratings;
    }
        */


        public function getPaginatedVendorProducts($vendorId, $perPage, $name = null, $category = null, $subcategory = null, $minPrice = 0, $maxPrice = PHP_INT_MAX)
        {
            $query = Product::where('vendor_id', $vendorId);

            if (!is_null($name) && !empty($name)) {
                $query->where('name', 'LIKE', "%$name%");
            }

            if (!is_null($category)) {
                $query->whereHas('subcategory.category', function ($q) use ($category) {
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
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'description' => $product->description,
                        'price' => $product->price,
                        'subcategory' => $product->subcategory->name ?? null,
                        'subcategory_id' => $product->subcategory->id ?? null,
                        'category' => $product->subcategory->category->name ?? null,
                        'category_id' => $product->subcategory->category->id ?? null,
                        'discount' => $product->discount->percentage ?? 0,
                        'images' => $product->images->pluck('imag'),
                        'attributes' => $product->attributes_data // استخدام الخاصية المضافة
                    ];
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
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'subcategory' => $product->subcategory->name ?? null,
                    'subcategory_id' => $product->subcategory->id ?? null,
                    'category' => $product->subcategory->category->name ?? null,
                    'discount' => $product->discount->percentage ?? 0,
                    'images' => $product->images->pluck('imag'),
                    'attributes' => $product->attributes_data // استخدام الخاصية المضافة
                ];
            });
        }

    // جلب المنتجات حسب الفئة الفرعية
    public function getProductsBySubCategory($subCategoryId, $perPage)
    {
        return Product::where('sub_category_id', $subCategoryId)
            ->with(['subcategory.Category', 'discount', 'images', 'ProductAttr.Attribute'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'subcategory' => $product->subcategory->name ?? null,
                    'subcategory_id' => $product->subcategory->id ?? null,
                    'category' => $product->subcategory->category->name ?? null,
                    'discount' => $product->discount->percentage ?? 0,
                    'images' => $product->images->pluck('imag'),
                    'attributes' => $product->attributes_data // استخدام الخاصية المضافة
                ];
            });
    }


    // جلب المنتجات حسب الاسم

    public function searchProducts($name = null, $minPrice = 0, $maxPrice = PHP_INT_MAX, $perPage = 5)
    {
        $query = Product::query();

        if (!is_null($name) && !empty($name)) {
            $query->where('name', 'LIKE', "%$name%");
        }

        if ($minPrice >= 0 && $maxPrice >= $minPrice) {
            $query->whereBetween('price', [$minPrice, $maxPrice]);
        }

        $products = $query->with(['subcategory.category', 'discount', 'images', 'ProductAttr.Attribute'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $products->getCollection()->transform(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'subcategory' => $product->subcategory->name ?? null,
                'subcategory_id' => $product->subcategory->id ?? null,
                'category' => $product->subcategory->category->name ?? null,
                'discount' => $product->discount->percentage ?? 0,
                'images' => $product->images->pluck('imag'),
                'attributes' => $product->attributes_data // استخدام الخاصية المضافة
            ];
        });

        return $products;
    }


    public function getProductsByVendor($vendorId, $perPage)
    {
        return Product::where('vendor_id', $vendorId)
            ->with(['subcategory.category', 'discount', 'images', 'ProductAttr.Attribute'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'subcategory' => $product->subcategory->name ?? null,
                    'subcategory_id' => $product->subcategory->id ?? null,
                    'category' => $product->subcategory->category->name ?? null,
                    'discount' => $product->discount->percentage ?? 0,
                    'images' => $product->images->pluck('imag'),
                    'attributes' => $product->attributes_data // استخدام الخاصية المضافة
                ];
            });
    }
}
