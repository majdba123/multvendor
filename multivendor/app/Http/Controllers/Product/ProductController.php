<?php


namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Requests\Product\FilterProduct;
use Illuminate\Support\Facades\Validator;

use App\Services\Product\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

use App\Models\SubCategory;
use App\Models\Category_Vendor;
use App\Models\ImagProduct;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }


    public function store(StoreProductRequest $request): JsonResponse
    {


        $vendor = Auth::user()->vendor->id;


        $product = $this->productService->createProduct($request->validated(),$vendor);

        $imageUrls = [];
        foreach ($request->images as $imageFile) {
            $imageName = Str::random(32) . '.' . $imageFile->getClientOriginalExtension();
            $imagePath = 'products_images/' . $imageName;
            $imageUrl = asset('storage/products_images/' . $imageName);

            // تخزين الصورة في التخزين
            Storage::disk('public')->put($imagePath, file_get_contents($imageFile));

            // إنشاء الصورة باستخدام الرابط الكامل
            ImagProduct::create([
                'product_id' => $product['id'], // استخدام ID من الناتج
                'imag' => $imageUrl,
            ]);

            // إضافة رابط الصورة إلى الاستجابة
            $imageUrls[] = $imageUrl;
        }

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product,
            'image_urls' => $imageUrls
        ], 201);
    }




    public function update(UpdateProductRequest $request, $id): JsonResponse
    {

        $vendor = Auth::user()->vendor->id;
        $product = Product::where('vendor_id',$vendor )->find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }



        $updatedProduct = $this->productService->updateProduct($request->validated(), $product);

        // التحقق مما إذا كان الطلب يحتوي على صور جديدة
        if ($request->has('images')) {
            // حذف الصور القديمة
            ImagProduct::where('product_id', $product->id)->delete();
            $imageUrls = [];
            foreach ($request->images as $imageFile) {
                $imageName = Str::random(32) . '.' . $imageFile->getClientOriginalExtension();
                $imagePath = 'products_images/' . $imageName;
                $imageUrl = asset('storage/products_images/' . $imageName);
                // تخزين الصورة في التخزين
                Storage::disk('public')->put($imagePath, file_get_contents($imageFile));
                // إنشاء الصورة باستخدام الرابط الكامل
                ImagProduct::create([
                    'product_id' => $product->id,
                    'imag' => $imageUrl,
                ]);
                // إضافة رابط الصورة إلى الاستجابة
                $imageUrls[] = $imageUrl;
            }
        }

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $updatedProduct,
            'image_urls' => $imageUrls ?? []
        ], 200);
    }




    public function latest_product(Request $request): JsonResponse
    {
        // عدد المنتجات في كل صفحة
        $perPage = $request->query('per_page', 5);
        // جلب أحدث المنتجات مع pagination
        $products = Product::orderBy('created_at', 'desc')->paginate($perPage);
        // تخصيص استجابة الـ pagination مع البيانات المطلوبة
        $response = $products->toArray();
        return response()->json($response);
    }









    public function getProductById($id)
    {
        $vendor = Auth::user()->vendor->id;
        $product = Product::where('vendor_id',$vendor )->find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product = $this->productService->getProductById($id);

        if ($product instanceof \Illuminate\Http\JsonResponse) {
            return $product;
        }

        return response()->json($product, 200);
    }


  /*  public function getProductRatings($id)
    {
        $ratings = $this->productService->getProductRatings($id);

        if ($ratings instanceof \Illuminate\Http\JsonResponse) {
            return $ratings;
        }

        return response()->json($ratings, 200);
    }
*/


    public function destroy($id): JsonResponse
    {
        $vendor = Auth::user()->vendor->id;
        $product = Product::where('vendor_id',$vendor )->find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $result = $this->productService->deleteProduct($id);
        return response()->json(['message' => $result['message']], $result['status']);
    }


    public function getVendorProducts(Request $request): JsonResponse
    {
        // إعداد قواعد التحقق
        $validator = Validator::make($request->all(), [
            'per_page' => 'integer|min:1', // عدد المنتجات في الصفحة
            'name' => 'string|nullable', // اسم المنتج
            'category_id' => 'integer|nullable|exists:categories,id', // معرف الفئة
            'subcategory_id' => 'integer|nullable|exists:sub_categories,id', // معرف الفئة الفرعية
            'min_price' => 'numeric|min:0|nullable', // الحد الأدنى للسعر
            'max_price' => 'numeric|min:0|nullable|gte:min_price', // الحد الأقصى للسعر
        ]);

        // التحقق من الأخطاء
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // جلب البيانات من الطلب بعد التحقق
        $vendorId = Auth::user()->vendor->id; // الحصول على معرف التاجر
        $perPage = $request->query('per_page', 5);
        $name = $request->query('name');
        $category = $request->query('category_id');
        $subcategory = $request->query('subcategory_id');
        $minPrice = $request->query('min_price', 0);
        $maxPrice = $request->query('max_price', PHP_INT_MAX);

        // استدعاء الخدمة لجلب المنتجات بناءً على المعايير
        $products = $this->productService->getPaginatedVendorProducts(
            $vendorId, $perPage, $name, $category, $subcategory, $minPrice, $maxPrice
        );

        // إرجاع الاستجابة
        return response()->json([
            'message' => 'Products fetched successfully',
            'products' => $products,
        ], 200);
    }


    public function getProductsByCategory(Request $request, $categoryId): JsonResponse
    {
        $perPage = $request->query('per_page', 5);
        $products = $this->productService->getProductsByCategory($categoryId, $perPage);

        return response()->json([
            'message' => 'Products fetched by category successfully',
            'products' => $products,
        ], 200);
    }

    // جلب المنتجات حسب الفئة الفرعية
    public function getProductsBySubCategory(Request $request, $subCategoryId): JsonResponse
    {
        $perPage = $request->query('per_page', 5);
        $products = $this->productService->getProductsBySubCategory($subCategoryId, $perPage);

        return response()->json([
            'message' => 'Products fetched by subcategory successfully',
            'products' => $products,
        ], 200);
    }

    // جلب المنتجات حسب الاسم
    public function searchProducts(Request $request): JsonResponse
    {
        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'per_page' => 'integer|min:1',
            'name' => 'string|nullable',
            'min_price' => 'numeric|min:0',
            'max_price' => 'numeric|min:0|gte:min_price',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $perPage = $request->query('per_page', 5);
        $name = $request->query('name');
        $minPrice = $request->query('min_price', 0);
        $maxPrice = $request->query('max_price', PHP_INT_MAX);

        // Retrieve products using service and criteria
        $products = $this->productService->searchProducts($name, $minPrice, $maxPrice, $perPage);

        if ($products->isEmpty()) {
            return response()->json([
                'message' => 'No products found matching the criteria',
            ], 404);
        }

        return response()->json([
            'message' => 'Products fetched successfully',
            'products' => $products,
        ], 200);
    }

    // جلب المنتجات حسب التاجر
    public function getProductsByVendor(Request $request, $vendorId): JsonResponse
    {
        $perPage = $request->query('per_page', 5);
        $products = $this->productService->getProductsByVendor($vendorId, $perPage);

        return response()->json([
            'message' => 'Products fetched by vendor successfully',
            'products' => $products,
        ], 200);
    }







}
