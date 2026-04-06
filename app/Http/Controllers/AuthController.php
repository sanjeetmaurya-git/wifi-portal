<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OtpRequest;
use Carbon\Carbon;
use App\Models\WifiUser;
use App\Models\WifiSession;
use App\Services\MikrotikService;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    protected $mikrotik;

    public function __construct(MikrotikService $mikrotik)
    {
        $this->mikrotik = $mikrotik;
    }

    // login page
    public function loginPage(Request $request)
    {
        $mac = $request->input('mac') ?: $request->input('mac-address') ?: 'unknown';
        $linkLogin = $request->input('link_login') ?? $request->input('link-login');

        // Check if this MAC has an active, non-expired session in our DB
        $activeSession = WifiSession::where('mac_address', $mac)
            ->where('expires_at', '>', Carbon::now())
            ->with('user')
            ->latest()
            ->first();

        if ($activeSession) {
            // 🔍 SYNC CHECK: Check if they are TRULY active on the MikroTik router
            $isTrulyActive = $this->mikrotik->isUserActive($activeSession->user->mobile);

            if (!$isTrulyActive && $linkLogin) {
                // Not active on router! Re-authenticate using our hidden POST form
                Log::info("[Auth] User in DB but inactive on router. Re-authenticating " . $activeSession->user->mobile);
                
                return view('mikrotik-login', [
                    'link_login' => $linkLogin,
                    'username'   => $activeSession->user->mobile,
                    'password'   => $activeSession->user->mobile, // Assuming password is same as mobile
                    'mac'        => $mac
                ]);
            }

            // If active on router, or we don't have link_login, show the status card
            return view('status', [
                'session' => $activeSession,
                'mobile' => $activeSession->user->mobile
            ]);
        }

        return view('login');
    }

    // send otp
    public function sendOtp(Request $request)
    {
        // Validate mobile
        $request->validate(['mobile' => 'required | digits:10 | regex:/^[6-9]\d{9}$/']);

        $mobile = $request->mobile;

        // 🔐 STEP 26 — OTP RATE LIMIT (ADD HERE)
        $recentOtp = OtpRequest::where('mobile', $mobile)
            ->where('created_at', '>', Carbon::now()->subMinutes(1))
            ->count();

        if ($recentOtp >= 3) {
            return back()->with('error', 'Too many OTP requests. Try after 1 minute.');
        }

        $otp = rand(100000, 999999);

        // save otp in database
        OtpRequest::create([
            'mobile' => $mobile,
            'otp_code' => $otp,
            'ip_address' => $request->ip(),
            'expires_at' => Carbon::now()->addMinutes(5)
        ]);

        // session([
        //     'otp'    => $otp,
        //     'mobile' => $mobile
        // ]);

        echo "Your OTP is: " . $otp;
        // compact('mobile')
        return view('verify', [
            'mobile' => $mobile,
            'otp' => $otp, // Pass OTP for development
            'mac' => $request->input('mac'),
            'ip' => $request->input('ip') ?? $request->ip(),
            'link_login' => $request->input('link_login') ?? $request->input('link-login')
        ]);
    }

    // verify otp
    // public function verifyOtp(Request $request, MikrotikService $mikrotik)
    // {
    //     // Validate request
    //     $request->validate([
    //         'mobile' => 'required|digits:10',
    //         'otp'    => 'required|digits:6',
    //     ]);

    //     // Find valid OTP
    //     $otpRecord = OtpRequest::where('mobile',     $request->mobile)
    //                             ->where('otp_code',  $request->otp)
    //                             ->where('verified',  false)
    //                             ->where('expires_at', '>', Carbon::now())
    //                             ->first();

    //     // OTP invalid or expired — redirect back with error
    //     if (!$otpRecord) {
    //         return redirect()->back()
    //             ->withInput($request->only('mobile', 'mac', 'ip', 'link_login'))
    //             ->with('error', 'Invalid or Expired OTP. Please try again.');
    //     }

    //     // Mark OTP as verified
    //     $otpRecord->update(['verified' => true]);       

    //     // Create or get WiFi user
    //     $user = WifiUser::firstOrCreate([
    //         'mobile' => $request->mobile
    //     ]);
    //     $user->update(['last_verified_at' => now()]);

    //     // Read MAC and IP from hidden form fields (sent by router)
    //     $mac = $request->input('mac') ?: '00:00:00:00:00:00';
    //     $ip  = $request->input('ip')  ?: $request->ip();

    //     // Capture device info from User-Agent header
    //     $agent = $request->header('User-Agent');

    //     //detect browser
    //     $browser = 'Unknown';
    //     if(str_contains($agent,'Chrome')) {
    //         $browser = 'Chrome';
    //     } elseif (str_contains($agent,'Firefox')) {
    //         $browser = 'Firefox';
    //     } 
    //     elseif (str_contains($agent,'Safari')) {
    //         $browser = 'Safari';
    //     }

    //     $os = 'Unknown';
    //     if (str_contains($agent,'Windows')) {
    //         $os = 'Windows';
    //     } elseif (str_contains($agent,'Android')) {
    //         $os = 'Android';
    //     } elseif (str_contains($agent,'Linux')) {
    //         $os = 'Linux';
    //     } elseif (str_contains($agent,'iPhone')) {
    //         $os = 'iOS';
    //     }
    //     // Create WiFi session
    //     try {
    //         // 🔥 STEP 18: Get Default Free Plan
    //         $plan = \App\Models\WifiPlan::where('name', 'Free Plan')->where('is_active', true)->first();

    //         // Fallback if seeder hasn't run
    //         $duration = $plan ? $plan->duration_minutes : 30;
    //         $rateLimit = $plan ? ($plan->upload_limit . '/' . $plan->download_limit) : null;

    //         \App\Models\WifiSession::create([
    //             'user_id'          => $user->id,
    //             'wifi_plan_id'     => $plan ? $plan->id : null,
    //             'mac_address'      => $mac,
    //             'ip_address'       => $ip,
    //             'device_name'      => $agent,
    //             'browser'          => $browser,
    //             'os'               => $os,
    //             'login_at'         => Carbon::now(),
    //             'expires_at'       => Carbon::now()->addMinutes($duration),
    //             'duration_minutes' => $duration,
    //         ]);
    //     } catch (\Throwable $e) {
    //         // ... (rest of catch remains same)
    //         logger()->error("WifiSession creation failed: " . $e->getMessage(), [
    //             'exception' => get_class($e),
    //             'file' => $e->getFile(),
    //             'line' => $e->getLine(),
    //             'trace' => $e->getTraceAsString(),
    //             'data' => [
    //                 'user_id' => $user->id,
    //                 'mac' => $mac,
    //                 'ip' => $ip,
    //             ]
    //         ]);
    //         throw $e;
    //     }

    //     // Sync user with router
    //     $routerSynced = false;

    //     try {
    //         $routerSynced = $mikrotik->addHotspotUser(
    //             $request->mobile,
    //             $request->otp,
    //             'default',
    //             $rateLimit ?? null
    //         );
    //     } catch (\Exception $e) {
    //         // router not connected yet
    //     }

    //     // 🔥 STEP 15 Router Redirect
    //     $linkLogin = $request->input('link_login');
    //     if ($linkLogin) {
    //         $loginUrl = $linkLogin .
    //             "?username=" . $request->mobile .
    //             "&password=" . $request->otp;
    //         return redirect($loginUrl);
    //     }

    //     // Redirect to success page
    //     // If router sent a link_login, pass it to success view for captive portal redirect
    //     return view('success', [
    //         'mobile'       => $request->mobile,
    //         'link_login'   => $request->input('link_login'),
    //         'routerSynced' => $routerSynced,
    //     ]);
    // }
    public function verifyOtp(Request $request, MikrotikService $mikrotik)
    {
        // 1️⃣ Validate request
        $request->validate([
            'mobile' => 'required|digits:10',
            'otp' => 'required|digits:6',
        ]);

        // 2️⃣ Check OTP
        $otpRecord = OtpRequest::where('mobile', $request->mobile)
            ->where('otp_code', $request->otp)
            ->where('verified', false)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$otpRecord) {
            return redirect()->back()
                ->withInput($request->only('mobile', 'mac', 'ip', 'link_login'))
                ->with('error', 'Invalid or Expired OTP. Please try again.');
        }

        // 3️⃣ Mark OTP verified
        $otpRecord->update(['verified' => true]);

        // 4️⃣ Create or get user
        $user = WifiUser::firstOrCreate([
            'mobile' => $request->mobile
        ]);

        $user->update([
            'last_verified_at' => now()
        ]);

        // 🔐 STEP 26 — LOGOUT PREVIOUS SESSIONS (ADD HERE)
        WifiSession::where('user_id', $user->id)
            ->whereNull('logout_at')
            ->update(['logout_at' => now()]);

        // 🔐 STEP 26 — MAC VALIDATION (ADD HERE)
        /**This will uncomment in final production  */
        /*
        if (!$request->mac) {
            return back()
            ->withInput($request->only('mobile', 'ip', 'link_login'))
            ->with('error', 'Invalid device');
        }*/

        // 5️⃣ Get MAC + IP (Enhanced detection)
        $mac = $request->input('mac') ?: $request->input('mac-address') ?: $request->input('mac_address') ?: 'unknown';
        $ip = $request->input('ip')  ?: $request->input('ip-address')  ?: $request->ip();

        // ✅ Store in session for PaymentController
        $linkLogin = $request->input('link_login') ?? $request->input('link-login');
        if (!$linkLogin) {
            $linkLogin = 'http://' . env('MIKROTIK_HOST', '192.168.88.1') . '/login';
        }

        session([
            'mobile' => $request->mobile,
            'mac' => $mac,
            'ip' => $ip,
            'link_login' => $linkLogin,
        ]);

        // Capture device info from User-Agent header
        $agent = $request->header('User-Agent');

        // detect browser
        $browser = 'Unknown';
        if (str_contains($agent, 'Chrome')) {
            $browser = 'Chrome';
        } elseif (str_contains($agent, 'Firefox')) {
            $browser = 'Firefox';
        } elseif (str_contains($agent, 'Safari') && !str_contains($agent, 'Chrome')) {
            $browser = 'Safari';
        }

        $os = 'Unknown';
        if (str_contains($agent, 'Windows')) {
            $os = 'Windows';
        } elseif (str_contains($agent, 'Android')) {
            $os = 'Android';
        } elseif (str_contains($agent, 'Linux')) {
            $os = 'Linux';
        } elseif (str_contains($agent, 'iPhone')) {
            $os = 'iOS';
        }

        // 6️⃣ Add user to MikroTik router (MUST happen BEFORE login attempt)
        try {
            $mikrotik->addHotspotUser(
                $request->mobile,
                $request->mobile, // username = password = mobile number
                'default',
                null
            );
        } catch (\Exception $e) {
            // Ignore if router not connected in dev mode
        }

        // 7️⃣ Create a FREE session immediately after OTP
        //    This powers the timer/status card and is required for loginPage auto-login
        $plan = \App\Models\WifiPlan::where('name', 'Free Plan')->where('is_active', true)->first();
        $duration = $plan ? $plan->duration_minutes : 30;

        $usedFreeThisMonth = WifiSession::where('user_id', $user->id)
            ->where('is_free', true)
            ->whereMonth('login_at', now()->format('m'))
            ->whereYear('login_at', now()->format('Y'))
            ->exists();

        if (!$usedFreeThisMonth) {
            WifiSession::create([
                'user_id' => $user->id,
                'mac_address' => $mac,
                'ip_address' => $ip,
                'login_at' => now(),
                'duration_minutes' => $duration,
                'expires_at' => now()->addMinutes($duration),
                'is_free' => true,
                'device_name' => $agent ?? 'Unknown',
                'browser' => $browser,
                'os' => $os,
            ]);
        }

        // 🔥 8️⃣ MikroTik Login — MUST be POST (GET redirect does NOT create Active session)
        // MikroTik sends 'link-login' (hyphen); HTML form converts it to 'link_login' (underscore).
        $linkLogin = $request->input('link_login') ?? $request->input('link-login');
        if (!$linkLogin) {
            $linkLogin = 'http://' . env('MIKROTIK_HOST', '192.168.88.1') . '/login';
        }

        if ($linkLogin) {
            // ✅ Direct Backend Redirect (100% Guaranteed)
            $separator = str_contains($linkLogin, '?') ? '&' : '?';
            $loginUrl = $linkLogin . $separator . 'username=' . urlencode($request->mobile) . '&password=' . urlencode($request->mobile);
            return redirect($loginUrl);
        }

        // Fallback: dev mode, no router connected
        return redirect('/plans')->with('success', 'OTP Verified! Please choose a plan.');

    }

    // disconnect
    public function disconnect(Request $request)
    {
        $mac = $request->input('mac');
        if (!$mac) {
            return redirect('/hotspot/login')->with('error', 'MAC address is required to disconnect.');
        }

        $session = \App\Models\WifiSession::where('mac_address', $mac)
            ->where('logout_at', null)
            ->latest()
            ->first();

        if ($session) {
            $session->update([
                'logout_at' => Carbon::now(),
                'expires_at' => Carbon::now(), // expire immediately
            ]);
        }

        return redirect('/hotspot/login?mac=' . $mac)->with('success', 'Disconnected successfully.');
    }

    // Step 19 HotSpot Login 
    public function hotspotLogin(Request $request)
    {
        $mac = $request->mac;
        $ip = $request->ip;

        // Step 1: Find existing session by MAC
        $session = WifiSession::where('mac_address', $mac)
            ->latest()
            ->first();

        if (!$session) {
            return view('login', compact('mac', 'ip'));
        }

        $user = $session->user;

        // Step 2: Check 15 days rule

        if (!$user->last_verified_at || $user->last_verified_at->diffInDays(now()) > 15) {
            return view('login', compact('mac', 'ip'));
        }

        // Step 3: Check active session
        // $isActive = now()->diffInMinutes($session->login_at) < $session->duration_minutes;
        $isActive = $session->expires_at > now();

        if ($isActive) {

            // AUTO LOGIN
            return $this->redirectToRouter($user, $request);
        }

        // Step 4: Plan expired → show plans
        return view('plans', compact('mac', 'ip'));
    }

    //Step 19 redirect to Login page 
    private function redirectToRouter($user, $request)
    {
        $linkLogin = $request->input('link_login') ?? $request->input('link-login') ?? 'http://' . env('MIKROTIK_HOST', '192.168.88.1') . '/login';

        $separator = str_contains($linkLogin, '?') ? '&' : '?';
        $loginUrl = $linkLogin . $separator . 'username=' . urlencode($user->mobile) . '&password=' . urlencode($user->mobile);
        
        return redirect($loginUrl);
    }

    //Step : 21 check authenticaion first time 
    // public function handleUserAccess($user, $mac, $ip)
    public function handleUserAccess($user, $mac, $ip, $agent = null, $browser = 'Unknown', $os = 'Unknown')
    {
        // 1️⃣ Check active session
        $activeSession = WifiSession::where('user_id', $user->id)
            ->where('mac_address', $mac)
            // ->whereRaw('TIMESTAMPDIFF(MINUTE, login_at, NOW()) < duration_minutes')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if ($activeSession) {
            // return redirect('/success'); // allow internet
            return view('success', [
                'mobile' => $user->mobile,
                'link_login' => request()->input('link_login'),
                'routerSynced' => false,
            ]);
        }

        // 2️⃣ Check free plan used this month
        $usedFree = WifiSession::where('user_id', $user->id)
            ->where('is_free', true)
            // ->whereMonth('login_at', now()->month)
            ->whereMonth('login_at', now()->format('m'))
            ->whereYear('login_at', now()->format('Y'))
            // ->whereYear('login_at', now()->year)
            ->exists();

        // 3️⃣ If NOT used → give FREE
        if (!$usedFree) {
            // Give FREE plan
            WifiSession::create([
                'user_id' => $user->id,
                'mac_address' => $mac,
                'ip_address' => $ip,
                'login_at' => now(),
                'duration_minutes' => 30,
                'expires_at' => \Carbon\Carbon::now()->addMinutes(30),
                // 'expires_at' => now()->addMinutes(30), // Step 21 Add this line
                'is_free' => true,
                // ✅ Ab yeh save honge
                'device_name' => $agent ?? 'Unknown',
                'browser' => $browser,
                'os' => $os,
            ]);

            // return redirect('/success');
            return view('success', [
                'mobile' => $user->mobile,
                'link_login' => request()->input('link_login'),
                'routerSynced' => false,
            ]);
        }

        // 3️⃣ No plan → redirect to plans
        return redirect('/plans');
    }


}
