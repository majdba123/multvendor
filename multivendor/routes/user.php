<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

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

});
