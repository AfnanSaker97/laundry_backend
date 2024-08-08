<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LaundryController;
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('register-Password', [RegisterController::class, 'registerPassword']);
//Route::middleware('auth:sanctum')->group( function () {

Route::get('launderies', [LaundryController::class, 'index']);
Route::get('search-launderies', [LaundryController::class, 'search']);
Route::get('laundry', [LaundryController::class, 'show']);

//});