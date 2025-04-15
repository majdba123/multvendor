<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Category\SubCategortController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\DiscountController;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware(['auth:sanctum','product_provider'])->group(function () {
    Route::prefix('categories')->group(function () {
        Route::get('get_all', [CategoryController::class, 'index']);
        Route::get('show/{id}', [CategoryController::class, 'show']);
    });

    Route::prefix('subcategories')->group(function () {
        Route::get('/getall_subcategory', [SubCategortController::class, 'index']); // عرض جميع الفئات الفرعية
        Route::get('get_by_category/{category_id}/', [SubCategortController::class, 'get_by_category']); // عرض جميع الفئات الفرعية
        Route::get('show/{id}', [SubCategortController::class, 'show']); // عرض الفئة الفرعية حسب ID
        Route::get('getSubCategory_Attributes/{subCategoryId}', [SubCategortController::class, 'getSubCategoryAttributes']);


    });

    Route::prefix('product')->group(function () {
    Route::get('/get_all', [ProductController::class, 'getVendorProducts']);
    Route::get('show/{product_id}', [ProductController::class, 'getProductById']);
    Route::post('store', [ProductController::class, 'store']);
    Route::post('update/{product_id}', [ProductController::class, 'update']);
    Route::delete('delete/{product_id}', [ProductController::class, 'destroy']);


    Route::prefix('discount')->group(function () {
        Route::post('/store/{product_id}', [DiscountController::class, 'store']); // POST /products/1/discount
        Route::put('/update/{product_id}', [DiscountController::class, 'update']); // POST /products/1/discount
        Route::post('/changeStatus/{product_id}', [DiscountController::class, 'changeStatus']); // تغيير الحالة
        Route::delete('/destroy/{product_id}', [DiscountController::class, 'destroy']); // حذف الخصم
    });

    });

    Route::prefix('orders')->group(function () {

        Route::get('get_all', [VendorController::class, 'getVendorOrders']);
        Route::get('get_all_by_status', [VendorController::class, 'getVendorOrdersByStatus']);
        Route::get('/get_all_by_produt_id/{product_id}', [VendorController::class, 'getOrdersByProductId']);
        Route::get('/get_all_by_user_id/{user_id}', [VendorController::class, 'getVendorOrdersByOrderProductStatus']);

    });





});
