<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\admin\PlanController;
use App\Http\Controllers\PaymentController;

Route::get('/', function () {
    return view('welcome');
});


//login, send otp, verify-Auth route
Route::get('/login',[AuthController::class,'loginPage']);
Route::post('/send-otp',[AuthController::class,'sendOtp']);
Route::post('/verify-otp',[AuthController::class,'verifyOtp']);

// Route::view('/success', 'success');


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
Route::get('/admin/system-logs',[AdminController::class,'systemLogs']); //step 12

// Step 14 Router/hotspot user login and 
Route::get('/hotspot/login', [AuthController::class,'loginPage']);
Route::post('/hotspot/verify-otp', [AuthController::class,'verifyOtp']);
Route::post('/hotspot/send-otp', [AuthController::class,'sendOtp']);
Route::post('/hotspot/disconnect', [AuthController::class, 'disconnect']);

// Step 17 connected with router
Route::get('/admin/router-status',[AdminController::class,'routerStatus']);

// Step 20 Admin create wifi_plans
// Route::prefix('admin')->group(function () {
//     Route::get('/plans', [PlanController::class, 'index']); //see existing plan 
//     Route::get('/plans/create', [PlanController::class, 'create']);  //create new plan  
//     Route::post('/plans', [PlanController::class, 'store']); //store new plan   
// });
Route::prefix('admin')->group(function () {
    Route::get('/plans', [PlanController::class, 'index']);
    Route::get('/plans/create', [PlanController::class, 'create']);
    Route::post('/plans', [PlanController::class, 'store']);

    // 👇 ADD THESE
    Route::get('/plans/{id}/edit', [PlanController::class, 'edit']);
    Route::put('/plans/{id}', [PlanController::class, 'update']);
    Route::delete('/plans/{id}', [PlanController::class, 'destroy']);
});

Route::get('/plans', [PlanController::class, 'userPlans']); 

// Step 22
Route::get('/admin/transactions', [AdminController::class, 'transactions']); //admin can see transaction
Route::post('/create-order', [PaymentController::class, 'createOrder']);
Route::get('/payment-page', [PaymentController::class, 'paymentPage']);
Route::post('/payment-success', [PaymentController::class, 'paymentSuccess']); //show payment sucess msg
Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
