<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Category\SubCategortController;
use App\Http\Controllers\Order\OrderController;

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

Route::middleware(['auth:sanctum'])->group(function () {



    Route::post('/profile/store', [ProfileController::class, 'storeProfile']);
    Route::post('/profile/update', [ProfileController::class, 'updateProfile']);
    Route::put('/my_info/update', [ProfileController::class, 'UpdateInfo']);
    Route::get('/my_info/get', [ProfileController::class, 'getUserInfo']);

    Route::prefix('categories')->group(function () {
        Route::get('get_all', [CategoryController::class, 'index']);
        Route::get('show/{id}', [CategoryController::class, 'show']);
    });


    Route::prefix('subcategories')->group(function () {
        Route::get('getall/', [SubCategortController::class, 'index']); // عرض جميع الفئات الفرعية
        Route::get('get_by_category/{category_id}/', [SubCategortController::class, 'get_by_category']); // عرض جميع الفئات الفرعية
        Route::get('show/{id}', [SubCategortController::class, 'show']); // عرض الفئة الفرعية حسب ID

    });

    Route::prefix('product')->group(function () {

        Route::get('/category/{categoryId}', [ProductController::class, 'getProductsByCategory']);
        Route::get('/subcategory/{subCategoryId}', [ProductController::class, 'getProductsBySubCategory']);
        Route::get('/search', [ProductController::class, 'searchProducts']);
        Route::get('/vendor/{vendorId}', [ProductController::class, 'getProductsByVendor']);
        });


    Route::prefix('orders')->group(function () {
            Route::post('/store', [OrderController::class, 'createOrder']);
            Route::get('/ByStatus', [OrderController::class, 'getUserOrders']);
            Route::get('/get_product/{order_id}', [OrderController::class, 'getProductOrder']);
        });


});
