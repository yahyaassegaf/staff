<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // dd(public_path('asdasd'));
    return view('welcome');
});


Route::get('/testing', [\App\Http\Controllers\TestingController::class, 'index']);

Route::get('/testing', [\App\Http\Controllers\TestingController::class, 'index']);






