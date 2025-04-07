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
    public function createProduct(array $data,$vendor_id)
    {

        // إنشاء المنتج وربطه بالعلاقة البولي مورفيك
        return Product::create([
            'name' => $data['name'],
            'description' => $data['description'],
            'price' => $data['price'],
            'sub_category_id' => $data['sub_category_id'],
            'vendor_id' => $vendor_id,

        ]);
    }


    public function updateProduct(array $data, $product)
    {

        // تحديث المنتج الموجود
        $product->update([
            'name' => $data['name'] ?? $product->name,
            'description' => $data['description'] ?? $product->description,
            'price' => $data['price'] ?? $product->price,
            'sub_category_id' => $data['sub_category_id'] ?? $product->sub_category_id,
        ]);

        return $product;
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
        $product = Product::with('images')->find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        return $product;
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
            // بناء الاستعلام لجلب المنتجات الخاصة بالتاجر فقط
            $query = Product::where('vendor_id', $vendorId);

            // إضافة شروط اختيارية بناءً على المعايير
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

            // ترتيب المنتجات حسب الأحدث
            $query->orderBy('created_at', 'desc');

            // جلب المنتجات مع العلاقات وتحويل البيانات
            return $query->with(['subcategory.category', 'discount', 'images'])
                ->paginate($perPage)
                ->transform(function ($product) {
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
                    ];
                });
        }


        public function getProductsByCategory($categoryId, $perPage)
        {
            return Product::whereHas('subcategory', function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->with(['subcategory', 'discount', 'images'])
            ->orderBy('created_at', 'desc') // ترتيب المنتجات حسب الأحدث
            ->paginate($perPage)
            ->transform(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'subcategory' => $product->subcategory->name ?? null,
                    'subcategory_id' => $product->subcategory->id ?? null,
                    'discount' => $product->discount->percentage ?? 0,
                    'images' => $product->images->pluck('imag'),
                ];
            });
        }

    // جلب المنتجات حسب الفئة الفرعية
    public function getProductsBySubCategory($subCategoryId, $perPage)
    {
        return Product::where('sub_category_id', $subCategoryId)
            ->with(['subcategory', 'discount', 'images'])
            ->orderBy('created_at', 'desc') // ترتيب المنتجات حسب الأحدث
            ->paginate($perPage)
            ->transform(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'subcategory' => $product->subcategory->name ?? null,
                    'discount' => $product->discount->percentage ?? 0,
                    'images' => $product->images->pluck('imag'),
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

        // ترتيب المنتجات حسب الأحدث
        $query->orderBy('created_at', 'desc');

        $products = $query->with(['subcategory', 'discount', 'images'])->paginate($perPage);

        $products->getCollection()->transform(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'subcategory' => $product->subcategory->name ?? null,
                'subcategory_id' => $product->subcategory->id ?? null,
                'discount' => $product->discount->percentage ?? 0,
                'images' => $product->images->pluck('imag'),
            ];
        });

        return $products;
    }



    // جلب المنتجات حسب التاجر
    public function getProductsByVendor($vendorId, $perPage)
    {
        return Product::where('vendor_id', $vendorId)
            ->with(['subcategory', 'discount', 'images'])
            ->orderBy('created_at', 'desc') // ترتيب المنتجات حسب الأحدث
            ->paginate($perPage);
    }

}
