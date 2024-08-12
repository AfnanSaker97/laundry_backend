<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LaundryController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AdminController;
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('register-Password', [RegisterController::class, 'registerPassword']);
//Route::middleware('auth:sanctum')->group( function () {

Route::post('register-admin', [AdminController::class, 'registerAdmin']);
Route::post('loginAdmin', [AdminController::class, 'loginAdmin']);


Route::get('users', [AdminController::class,'index']);
Route::get('Driver', [AdminController::class,'getDriver']);

Route::get('launderies', [LaundryController::class, 'index']);
Route::get('search-launderies', [LaundryController::class, 'search']);
Route::get('laundry', [LaundryController::class, 'show']);


Route::middleware('auth:sanctum')->group(function() {

//Address
Route::post('Address', [AddressController::class, 'store']);

Route::get('logout', [RegisterController::class, 'logout']);

Route::get('User', [RegisterController::class, 'getUser']);


Route::post('order', [OrderItemController::class, 'store']);
Route::get('order', [OrderItemController::class, 'index']);
Route::get('OrderDetails', [OrderController::class, 'OrderDetails']);
});