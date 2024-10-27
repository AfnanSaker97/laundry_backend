<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('test', function () {
    return view('test');
});


Route::get('receive', function () {
    return view('receive');
});
