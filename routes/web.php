<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\admin\PlanController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\UserController;
use App\Models\WifiPlan;

Route::get('/', function () {
    $plans = WifiPlan::where('is_active', true)->get();
    return view('welcome', compact('plans'));
});


//login, send otp, verify-Auth route
Route::get('/login', [AuthController::class, 'loginPage'])->name('login');
Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/register-save', [AuthController::class, 'saveRegistration'])->name('register.save');
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

// Route::view('/success', 'success');


// Admin Authentication Routes
Route::get('/admin/login', [AdminController::class, 'showLoginForm']);
Route::post('/admin/login', [AdminController::class, 'login']);

// Admin Protected Routes
Route::middleware(['admin.auth'])->group(function () {
    //admin dashboard (admin, users-list, sessions, otp-logs)
    Route::get('/admin', [AdminController::class, 'dashboard']);
    Route::get('/admin/users', [AdminController::class, 'users']);
    Route::get('/admin/sessions', [AdminController::class, 'sessions']);
    Route::get('/admin/otp-logs', [AdminController::class, 'otpLogs']);

    // admin analytics route 
    Route::get('/admin/analytics-data', [AdminController::class, 'analyticsData']);

    Route::get('/admin/active-sessions', [AdminController::class, 'activeSessions']);
    Route::post('/admin/disconnect-user', [AdminController::class, 'disconnectUser']);
    Route::get('/admin/usage', [AdminController::class, 'usageStats']);  //step 11
    Route::get('/admin/system-logs', [AdminController::class, 'systemLogs']); //step 12
    Route::get('/admin/router-status', [AdminController::class, 'routerStatus']);

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
    Route::post('/admin/clear-all-sessions', [AdminController::class, 'clearAllSessions']);
});

// User Plan & Payment Routes
Route::get('/plans', [PlanController::class, 'userPlans']);
Route::post('/create-order', [PaymentController::class, 'createOrder']);
Route::get('/payment-page', [PaymentController::class, 'paymentPage']);
Route::post('/payment-success', [PaymentController::class, 'paymentSuccess']); //show payment sucess msg
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

//Step 24 
Route::get('/my-plans', [UserController::class, 'myPlans']);
Route::get('/profile', [UserController::class, 'profile']);
Route::post('/add-secondary-number', [UserController::class, 'addSecondaryNumber']);

//Step 25 
Route::get('/usage', [App\Http\Controllers\UsageController::class, 'index'])->name('usage');

Route::get('/dashboard', function () {
    return redirect()->route('usage');
})->middleware('check.plan');

//check active session 
Route::get('/check-session', [UserController::class, 'checkSession']);

//Step 26

Route::post('/send-otp', [AuthController::class, 'sendOtp'])->middleware('throttle:3,1'); // 3 Otp request per minute

// 🔬 Diagnostic: Full session + DB + MikroTik status
Route::get('/debug', function (Illuminate\Http\Request $request) {
    $mobile    = $request->query('mobile') ?? session('mobile');
    $mac       = $request->query('mac')    ?? session('mac');
    $linkLogin = session('link_login');
    $activateLL = session('activate_link_login');

    // If still no mobile, try to find user by MAC
    if (!$mobile && $mac) {
        $userByMac = \App\Models\WifiUser::where('mac_address', $mac)->first();
        if ($userByMac) $mobile = $userByMac->mobile;
    }

    // DB session
    $user = $mobile ? \App\Models\WifiUser::where('mobile', $mobile)->first() : null;
    $dbSession = $user
        ? \App\Models\WifiSession::where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->whereNull('logout_at')
            ->with('plan')
            ->latest()
            ->first()
        : null;

    // MikroTik connection test
    try {
        $mikrotik  = new \App\Services\MikrotikService();
        $connected = $mikrotik->connect();
        $mtStatus  = $connected === true ? 'MOCK MODE (MIKROTIK_CONNECTED=false)' : 'CONNECTED ✅';
        // Try to check if user exists in MikroTik
        $mtUserExists = $mobile ? ($mikrotik->userExists($mobile) ? 'YES ✅' : 'NO ❌') : 'N/A';
        // Active sessions from MikroTik
        $mtActiveSessions = $mikrotik->getActiveUsers();
        $mtIsOnline = collect($mtActiveSessions)->contains('user', $mobile) ? 'YES ✅' : 'NO ❌';
    } catch (\Exception $e) {
        $mtStatus       = 'FAILED ❌: ' . $e->getMessage();
        $mtUserExists   = 'N/A';
        $mtIsOnline     = 'N/A';
        $mtActiveSessions = [];
    }

    return response()->json([
        '=== SESSION ===' => '---',
        'session_mobile'        => $mobile       ?? '❌ NOT SET',
        'session_mac'           => $mac          ?? '❌ NOT SET',
        'link_login'            => $linkLogin    ?? '❌ NOT SET (portal not accessed via MikroTik redirect)',
        'activate_link_login'   => $activateLL   ?? '❌ NOT SET',

        '=== DATABASE ===' => '---',
        'db_user_found'         => $user         ? "✅ {$user->full_name} ({$user->mobile})" : '❌ NOT FOUND',
        'db_active_session'     => $dbSession
            ? "✅ Plan: {$dbSession->plan->name} | Expires: {$dbSession->expires_at}"
            : '❌ NO ACTIVE SESSION IN DB',

        '=== MIKROTIK ===' => '---',
        'mikrotik_api'          => $mtStatus,
        'user_in_mikrotik'      => $mtUserExists,
        'user_online_now'       => $mtIsOnline,
        'active_count'          => count($mtActiveSessions),

        '=== CONFIG ===' => '---',
        'app_url'               => config('app.url'),
        'mikrotik_host'         => env('MIKROTIK_HOST'),
        'mikrotik_connected'    => env('MIKROTIK_CONNECTED'),
        'mikrotik_user'         => env('MIKROTIK_USER'),
    ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
});

// Legacy
Route::get('/test-mikrotik', [\App\Http\Controllers\AuthController::class, 'testMikrotik']);

// Connection Success Page
Route::get('/success', [AuthController::class, 'success'])->name('success');

// ✅ KEY ROUTE: Fresh page that auto-POSTs to MikroTik after Razorpay payment
Route::get('/activate-internet', [PaymentController::class, 'activateInternet']);

// 📡 SAAS MODE: MikroTik Polling API (No Port Forwarding needed)
Route::get('/api/router/fetch-commands', function (Illuminate\Http\Request $request) {
    $routerId = $request->query('router_id');
    
    // Find pending commands. 
    // If router_id is provided, look for that router OR commands with NULL router_id.
    $commands = \App\Models\MikrotikCommand::where('status', 'pending')
        ->where(function($query) use ($routerId) {
            if ($routerId) {
                $query->where('router_id', $routerId)->orWhereNull('router_id');
            }
        })
        ->get();

    if ($commands->isEmpty()) {
        return "";
    }

    $script = "/log warning \"Server: Executing " . $commands->count() . " commands\";\r\n";
    foreach ($commands as $cmd) {
        $script .= $cmd->command . "\r\n";
        $cmd->update([
            'status' => 'executed',
            'executed_at' => now(),
            'attempts' => $cmd->attempts + 1
        ]);
    }

    return response($script, 200)->header('Content-Type', 'text/plain');
});