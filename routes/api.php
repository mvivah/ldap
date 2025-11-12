<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\InvoiceController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\CustomerController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/status', function () {
    return response()->json(['status' => 'API is running']);
});
Route::get('/hello', function () {
    return ['message' => 'Hello, API World!'];
});

//Route group prefix 'v1' and namespace 'Api\V1'
Route::group(['prefix' =>'v1', 'namespace' =>'App\Http\Controllers\Api\V1'], function () {
Route::apiResource('posts',PostController::class);
Route::apiResource('products',ProductController::class);
Route::apiResource('customers',CustomerController::class);
Route::apiResource('invoices',InvoiceController::class);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password/{id}', [AuthController::class, 'resetPassword']);
});