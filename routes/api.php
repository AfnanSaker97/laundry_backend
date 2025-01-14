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
use App\Http\Controllers\AdvertisementMediaController;
use App\Http\Controllers\OrderTypeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TruckController;
use App\Http\Controllers\LaundryMediaController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Broadcast;
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::get('report-order/{laundry_id?}', [OrderController::class, 'export']);


Route::post('send-location', [CarController::class, 'updateCoordinates']);
Route::post('update-location', [CarController::class, 'periodicallyUpdateLocation']);
Route::post('truck/{truckId}', [TruckController::class,'updateLocation']);

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



Route::middleware('auth:sanctum')->group(function() {
//Car
Route::get('cars', [CarController::class, 'index']);
//Route::get('Cars', [CarController::class, 'getCars']);
Route::get('search-cars', [CarController::class, 'search']);
Route::get('CarById', [CarController::class, 'show']);
Route::post('Car', [CarController::class, 'store']);
Route::post('updateCar', [CarController::class, 'update']);
Route::post('UpdateStatusCar', [CarController::class, 'UpdateStatusCar']);


Route::get('order-admin', [OrderController::class, 'index']);
Route::get('search-orders', [OrderController::class, 'search']);
Route::get('OrderByLaundryId', [OrderController::class, 'OrderByLaundryId']);
Route::get('filter-orders', [OrderController::class, 'FilterOrder']);

Route::post('createDriver', [DriverController::class, 'createDriver']); 
Route::delete('deleteDriver', [DriverController::class, 'destroy']); 
Route::get('DriverById', [DriverController::class, 'show']); 
Route::post('updateDriver', [DriverController::class, 'update']); 
Route::get('getDriversByLaundryId', [DriverController::class, 'getDriversByLaundryId']); 




//Address
Route::post('Address', [AddressController::class, 'store']);
Route::delete('Address-delete', [AddressController::class,'destroy']);
Route::post('Address-update', [AddressController::class,'update']);
Route::get('AddressById', [AddressController::class,'show']);
Route::get('addressUser', [AddressController::class,'addressUser']);
Route::post('UpdateStatusAddress', [AddressController::class,'UpdateStatusAddress']);


Route::get('Service', [ServiceController::class, 'index']);
Route::post('service', [ServiceController::class, 'store']); //super admin
Route::post('update-service', [ServiceController::class, 'update']); //super admin
Route::delete('delete-service', [ServiceController::class, 'delete']); //super admin
Route::get('service-id', [ServiceController::class, 'show']);




Route::get('launderies', [LaundryController::class, 'index']);//super admin
Route::get('search-launderies', [LaundryController::class, 'search']); //users
Route::get('laundry', [LaundryController::class, 'show']);





Route::get('Nearby', [LaundryController::class, 'getLaundriesByProximity']);


//LaundryMedia
Route::post('laundry-media', [LaundryMediaController::class, 'store']); //admin
Route::delete('laundry-media', [LaundryMediaController::class, 'destroy']); //admin


Route::get('getOrderByProximity', [OrderController::class, 'getOrderByProximity']); //driver
Route::get('filterOrder', [OrderController::class, 'filterOrder']); //supre
Route::post('confirm-Order', [OrderController::class, 'store']);//admin
Route::post('totalPrice', [OrderItemController::class, 'totalPrice']);//admin




    //Notification
    Route::get('markAsRead', [NotificationController::class, 'markAsRead']);
    Route::get('getNotificationsForUser', [NotificationController::class, 'getNotificationsForUser']);

    

Route::get('laundryItem', [LaundryItemController::class, 'index']);
Route::get('laundryItemById', [LaundryItemController::class, 'show']);
Route::get('getLaundryItem', [LaundryItemController::class, 'getLaundryItem']);
Route::get('getLaundryItem-id', [LaundryItemController::class, 'showLaundryItem']);


Route::post('laundryItem', [LaundryItemController::class, 'store']);//super admin
Route::post('update-laundryItem', [LaundryItemController::class, 'UpdateItem']);//super admin
Route::delete('delete-laundryItem', [LaundryItemController::class, 'deleteItem']);//super admin




Route::get('OrderType', [OrderTypeController::class, 'index']);
//Advertisement
Route::get('ads', [AdvertisementController::class,'index']);
Route::post('Advertisement', [AdvertisementController::class,'store']); // admin
Route::get('getAdvertisement', [AdvertisementController::class,'getAdvertisement']);//admin
Route::get('confirmAdvertisement', [AdvertisementController::class,'confirmAdvertisement']);//super admin
Route::get('ads-id', [AdvertisementController::class,'show']);
Route::post('Advertisement-update', [AdvertisementController::class,'update']);//admin

//AdvertisementMedia
Route::post('AdvertisementMedia', [AdvertisementMediaController::class,'store']);//admin
Route::delete('AdvertisementMedia', [AdvertisementMediaController::class, 'destroy']);



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

//SuperAdmin
Route::get('search-users', [UserController::class, 'search']);
Route::get('Filter-users', [UserController::class,'index']);//super admin
Route::get('userById', [UserController::class,'show']);//super admin



//laundry Admin

Route::get('LaundryByAdmin', [LaundryController::class, 'LaundryByAdmin']);
Route::post('Laundry', [LaundryController::class, 'store']);
Route::post('Laundry-update', [LaundryController::class, 'update']);
Route::post('UpdateStatusLaundery', [LaundryController::class, 'UpdateStatusLaundery']);
Route::post('UpdateUrgent', [LaundryController::class, 'UpdateUrgent']);




//MyOrder
Route::get('MyOrders', [OrderController::class, 'MyOrder']);
Route::get('filterMyOrder', [OrderController::class, 'filterMyOrder']);

Route::get('filterMyOrderUser', [OrderController::class, 'filterMyOrderUser']);

Route::delete('order', [OrderController::class, 'destroy']);


Route::post('update-Laundryprice', [LaundryItemController::class,'update']);

Route::get('getTotal', [OrderController::class, 'getTotal']);

Route::get('order-stats', [OrderController::class, 'getOrderStats']);


Route::post('sendNotification', [CarController::class,'sendNotification']);//super admin

});

