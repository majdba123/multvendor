<?php
namespace App\Http\Controllers\Category;
use App\Http\Controllers\Controller;

use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Services\Category\CategoryService;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }
    public function index(): JsonResponse
    {
        // استقبال النوع من معامل الاستعلام بعد التحقق
        // جلب الكاتيغوري بناءً على النوع
        $categories = $this->categoryService->getAll();

        return response()->json($categories);
    }


    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->create($request->validated());
        return response()->json(['message' => 'Category created successfully', 'category' => $category], 201);
    }

    public function show($id): JsonResponse
    {
        $category = $this->categoryService->getById($id);
        return response()->json($category);
    }

    public function update(UpdateCategoryRequest $request, $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $category = $this->categoryService->update($category, $request->validated());
        return response()->json(['message' => 'Category updated successfully', 'category' => $category]);
    }

    public function destroy($id): JsonResponse
    {
        $category = Category::findOrFail($id);

        $this->categoryService->delete($category);
        return response()->json(['message' => 'Category deleted successfully']);
    }


}
