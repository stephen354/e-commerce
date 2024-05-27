<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RatingController;
use App\Http\Middleware\ApiAuthMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
// Route::middleware(ApiAuthMiddleware::class)->group(function () {
Route::delete('/customer/logout/{email}', [CustomerController::class, 'logout']);

Route::post('/cart', [CartController::class, 'create']);
Route::get('/cart/{id}', [CartController::class, 'cart'])->where('id', '[0-9]+');
Route::get('/cart/product/{id}', [CartController::class, 'get'])->where('id', '[0-9]+');
Route::delete('/cart/{id}', [CartController::class, 'delete'])->where('id', '[0-9]+');

Route::post('/payment', [PaymentController::class, 'create']);
Route::delete('/payment/{id}', [PaymentController::class, 'delete'])->where('id', '[0-9]+');
Route::get('/payment/show/{id}', [PaymentController::class, 'getPayment'])->where('id', '[0-9]+');
Route::put('/payment/cancelorder', [PaymentController::class, 'cancelOrder']);
Route::post('/payment/updatebayar', [PaymentController::class, 'updateBayar']);
Route::put('/payment/updatekemas', [PaymentController::class, 'updateKemas']);
Route::put('/payment/updatekirim', [PaymentController::class, 'updateKirim']);
Route::put('/payment/selesai', [PaymentController::class, 'updateSelesai']);

Route::get('/payment/{id}', [PaymentController::class, 'allpayment'])->where('id', '[0-9]+');

Route::post('/payment/rating', [RatingController::class, 'create']);
// });
Route::post('/customer', [CustomerController::class, 'register']);
Route::post('/customer/login', [CustomerController::class, 'login']);

Route::post('/product', [ProductController::class, 'create']);
Route::get('/product', [ProductController::class, 'show']);
Route::get('/product/{id}', [ProductController::class, 'get'])->where('id', '[0-9]+');
Route::get('/product/rating/{id}', [ProductController::class, 'showRating'])->where('id', '[0-9]+');
Route::put('/product/{id}', [ProductController::class, 'update'])->where('id', '[0-9]+');
Route::delete('/product/{id}', [ProductController::class, 'delete'])->where('id', '[0-9]+');
Route::get('/product/category/{category}', [ProductController::class, 'byCategory']);

Route::post('/category', [CategoryController::class, 'create']);
Route::get('/category', [CategoryController::class, 'show']);
Route::get('/category/{id}', [CategoryController::class, 'get'])->where('id', '[0-9]+');
Route::put('/category', [CategoryController::class, 'update']);
Route::delete('/category/{id}', [CategoryController::class, 'delete'])->where('id', '[0-9]+');
