<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('welcome');
});


//login, send otp, verify-Auth route
Route::get('/login',[AuthController::class,'loginPage']);
Route::post('/send-otp',[AuthController::class,'sendOtp']);
Route::post('/verify-otp',[AuthController::class,'verifyOtp']);