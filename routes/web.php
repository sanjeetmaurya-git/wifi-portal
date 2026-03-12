<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;

Route::get('/', function () {
    return view('welcome');
});


//login, send otp, verify-Auth route
Route::get('/login',[AuthController::class,'loginPage']);
Route::post('/send-otp',[AuthController::class,'sendOtp']);
Route::post('/verify-otp',[AuthController::class,'verifyOtp']);

// Route::view('/sucess', 'success');

//admin dashboard (admin, users-list, sessions, otp-logs)
Route::get('/admin', [AdminController::class,'dashboard']);
Route::get('/admin/users', [AdminController::class,'users']);
Route::get('/admin/sessions', [AdminController::class,'sessions']);
Route::get('/admin/otp-logs', [AdminController::class,'otpLogs']);

// admin analytics route 
Route::get('/admin/analytics-data', [AdminController::class,'analyticsData']);

Route::get('/admin/active-sessions', [AdminController::class,'activeSessions']);
Route::post('/admin/disconnect-user', [AdminController::class,'disconnectUser']);
Route::get('/admin/usage',[AdminController::class,'usageStats']);  //step 11

