<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // dd(public_path('asdasd'));
    return view('welcome');
});
