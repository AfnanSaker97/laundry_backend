<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LaundryController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\AddressController;
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('register-Password', [RegisterController::class, 'registerPassword']);
//Route::middleware('auth:sanctum')->group( function () {

Route::get('launderies', [LaundryController::class, 'index']);
Route::get('search-launderies', [LaundryController::class, 'search']);
Route::get('laundry', [LaundryController::class, 'show']);


Route::middleware('auth:sanctum')->group(function() {

//Address
Route::post('Address', [AddressController::class, 'store']);




Route::post('order', [OrderItemController::class, 'store']);
Route::get('order', [OrderItemController::class, 'index']);
});