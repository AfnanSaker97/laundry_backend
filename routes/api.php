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
use App\Http\Controllers\LaundryItemController;
use App\Http\Controllers\AdvertisementController;
use App\Http\Controllers\OrderTypeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Auth\GoogleController;
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('register', [RegisterController::class, 'register']);
Route::post('register-Password', [RegisterController::class, 'registerPassword']);
Route::post('login', [RegisterController::class, 'login']);
Route::post('verify', [RegisterController::class, 'verify']);


Route::post('auth/google', [GoogleController::class, 'handleGoogleCallback']);

//Route::middleware('auth:sanctum')->group( function () {
 

  //  Route::get('auth/google', [GoogleController::class, 'redirectToGoogle']);
    //Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
    


Route::post('register-admin', [AdminController::class, 'registerAdmin']);
Route::post('loginAdmin', [AdminController::class, 'loginAdmin']);

Route::get('Filter-users', [AdminController::class,'index']);//super admin

Route::get('order-admin', [OrderController::class, 'index']);
Route::post('createDriver', [AdminController::class, 'createDriver']); 


//Car
Route::get('CarByLaundry', [CarController::class, 'index']);
Route::middleware('auth:sanctum')->group(function() {

//Address
Route::post('Address', [AddressController::class, 'store']);
Route::delete('Address-delete', [AddressController::class,'destroy']);
Route::post('Address-update', [AddressController::class,'update']);
Route::get('AddressById', [AddressController::class,'show']);
Route::get('addressUser', [AddressController::class,'addressUser']);
Route::post('UpdateStatusAddress', [AddressController::class,'UpdateStatusAddress']);


Route::get('launderies', [LaundryController::class, 'index']);//super admin
Route::get('search-launderies', [LaundryController::class, 'search']); //users
Route::get('laundry', [LaundryController::class, 'show']);




Route::get('Nearby', [LaundryController::class, 'getLaundriesByProximity']);
Route::get('getOrderByProximity', [OrderController::class, 'getOrderByProximity']); //driver
Route::get('filterOrder', [OrderController::class, 'filterOrder']); //supre
Route::post('confirm-Order', [OrderController::class, 'store']);//admin
Route::post('totalPrice', [OrderItemController::class, 'totalPrice']);//admin




    //Notification
    Route::get('markAsRead', [NotificationController::class, 'markAsRead']);
    Route::get('getNotificationsForUser', [NotificationController::class, 'getNotificationsForUser']);

    

Route::get('laundryItem', [LaundryItemController::class, 'index']);
Route::get('laundryItemById', [LaundryItemController::class, 'show']);


Route::get('OrderType', [OrderTypeController::class, 'index']);

Route::get('Advertisement', [AdvertisementController::class,'index']);
Route::post('clickAdvertisement', [AdvertisementController::class,'clickAdvertisement']);


Route::get('logout', [RegisterController::class, 'logout']);

Route::get('User', [RegisterController::class, 'getUser']);
Route::post('User-update', [RegisterController::class, 'update']);
Route::delete('user-delete', [RegisterController::class, 'destroy']);

Route::post('Driver-update', [RegisterController::class, 'update']);

Route::post('order', [OrderItemController::class, 'store']);
Route::get('order', [OrderItemController::class, 'index']);
Route::get('OrderDetails', [OrderController::class, 'OrderDetails']);
Route::get('ordersUser', [OrderController::class, 'ordersUser']);



//LaundrySuperAdmin
Route::get('laundries-super', [LaundryController::class, 'LaundrySuperAdmin']);

//laundry Admin

Route::get('LaundryByAdmin', [LaundryController::class, 'LaundryByAdmin']);
Route::post('Laundry', [LaundryController::class, 'store']);
Route::post('Laundry-update', [LaundryController::class, 'update']);
Route::post('UpdateStatusLaundery', [LaundryController::class, 'UpdateStatusLaundery']);


//MyOrder
Route::get('MyOrders', [OrderController::class, 'MyOrder']);
Route::get('filterMyOrder', [OrderController::class, 'filterMyOrder']);

Route::get('filterMyOrderUser', [OrderController::class, 'filterMyOrderUser']);



Route::post('update-Laundryprice', [LaundryItemController::class,'update']);

Route::get('getTotal', [OrderController::class, 'getTotal']);

Route::get('order-stats', [OrderController::class, 'getOrderStats']);


Route::post('sendNotification', [CarController::class,'sendNotification']);//super admin
});

