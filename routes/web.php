<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\admin\PlanController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return view('welcome');
});


//login, send otp, verify-Auth route
Route::get('/login',[AuthController::class,'loginPage'])->name('login');
Route::post('/send-otp',[AuthController::class,'sendOtp']);
Route::post('/register-save',[AuthController::class,'saveRegistration'])->name('register.save');
Route::post('/verify-otp',[AuthController::class,'verifyOtp']);

// Route::view('/success', 'success');


// Admin Authentication Routes
Route::get('/admin/login', [AdminController::class, 'showLoginForm']);
Route::post('/admin/login', [AdminController::class, 'login']);

// Admin Protected Routes
Route::middleware(['admin.auth'])->group(function () {
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
    Route::get('/admin/router-status',[AdminController::class,'routerStatus']);

    // Step 20 Admin create wifi_plans
    Route::prefix('admin')->group(function () {
        Route::get('/plans', [PlanController::class, 'index']);
        Route::get('/plans/create', [PlanController::class, 'create']);
        Route::post('/plans', [PlanController::class, 'store']);
    
        // 👇 ADD THESE
        Route::get('/plans/{id}/edit', [PlanController::class, 'edit']);
        Route::put('/plans/{id}', [PlanController::class, 'update']);
        Route::delete('/plans/{id}', [PlanController::class, 'destroy']);
    });

    // Step 22
    Route::get('/admin/transactions', [AdminController::class, 'transactions']); //admin can see transaction
    Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');

    // Step 23 
    Route::get('/admin/revenue-data', [AdminController::class, 'revenueData']);
});

// User Plan & Payment Routes
Route::get('/plans', [PlanController::class, 'userPlans']); 
Route::post('/create-order', [PaymentController::class, 'createOrder']);
Route::get('/payment-page', [PaymentController::class, 'paymentPage']);
Route::post('/payment-success', [PaymentController::class, 'paymentSuccess']); //show payment sucess msg
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Step 24 
Route::get('/my-plans', [UserController::class, 'myPlans']);

//Step 25 
Route::get('/dashboard', function () {
    return view('user.dashboard');
})->middleware('check.plan');

Route::get('/dashboard', function () {
    return view('user.dashboard');
})->middleware('check.plan');

//check active session 
Route::get('/check-session', [UserController::class, 'checkSession']);

//Step 26

Route::post('/send-otp', [AuthController::class, 'sendOtp'])->middleware('throttle:3,1'); // 3 Otp request per minute

// Diagnosis
Route::get('/test-mikrotik', [AuthController::class, 'testMikrotik']);

// Connection Success Page
Route::get('/success', [AuthController::class, 'success'])->name('success');

// ✅ KEY ROUTE: Fresh page that auto-POSTs to MikroTik after Razorpay payment
// JS in payment.blade.php redirects here — full page load = reliable form auto-submit
Route::get('/activate-internet', [PaymentController::class, 'activateInternet']);

// 🔬 Diagnostic: test MikroTik connection + show what link_login is in session
Route::get('/test-api', function () {
    $linkLogin = session('link_login');
    $mobile    = session('mobile');
    $mac       = session('mac');
    try {
        $mikrotik = new \App\Services\MikrotikService();
        $connected = $mikrotik->connect();
        $status = $connected === true ? 'MOCK MODE (MIKROTIK_CONNECTED=false)' : 'CONNECTED ✅';
    } catch (\Exception $e) {
        $status = 'FAILED ❌: ' . $e->getMessage();
    }
    return response()->json([
        'mikrotik_api'  => $status,
        'session_mobile'=> $mobile ?? 'NOT SET',
        'session_mac'   => $mac    ?? 'NOT SET',
        'link_login'    => $linkLogin ?? 'NOT SET — user must access via MikroTik redirect first',
        'env_host'      => env('MIKROTIK_HOST'),
        'env_connected' => env('MIKROTIK_CONNECTED'),
    ]);
});