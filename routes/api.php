<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LaundryController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CarController;
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('register-Password', [RegisterController::class, 'registerPassword']);
//Route::middleware('auth:sanctum')->group( function () {

Route::post('register-admin', [AdminController::class, 'registerAdmin']);
Route::post('loginAdmin', [AdminController::class, 'loginAdmin']);

Route::get('Filter-users', [AdminController::class,'index']);

Route::get('launderies', [LaundryController::class, 'index']);
Route::get('search-launderies', [LaundryController::class, 'search']);
Route::get('laundry', [LaundryController::class, 'show']);


Route::get('order-admin', [OrderController::class, 'index']);
Route::post('createDriver', [AdminController::class, 'createDriver']);


//Car
Route::get('CarByLaundry', [CarController::class, 'index']);
Route::middleware('auth:sanctum')->group(function() {

//Address
Route::post('Address', [AddressController::class, 'store']);

Route::get('Nearby', [LaundryController::class, 'getLaundriesByProximity']);
Route::get('getOrderByProximity', [OrderController::class, 'getOrderByProximity']);


Route::get('logout', [RegisterController::class, 'logout']);

Route::get('User', [RegisterController::class, 'getUser']);
Route::post('User-update', [RegisterController::class, 'update']);
Route::post('Driver-update', [RegisterController::class, 'update']);

Route::post('order', [OrderItemController::class, 'store']);
Route::get('order', [OrderItemController::class, 'index']);
Route::get('OrderDetails', [OrderController::class, 'OrderDetails']);
});