<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OtpRequest;
use Carbon\Carbon;
use App\Models\WifiUser;
use App\Models\WifiSession;
use App\Services\MikrotikService;
use App\Models\WifiPlan;
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
        $mac = $request->input('mac') ?: $request->input('mac-address');
        $linkLogin = $request->input('link_login') ?? $request->input('link-login');
        $ip = $request->input('ip') ?: $request->input('ip-address') ?: $request->ip();

        // ─────────────────────────────────────────────────────────────
        // 🚫 Step 30: DATA-EXHAUSTED DETECTION
        // MikroTik sends error=Traffic+limit+reached when a user hits
        // their daily / total byte limit and gets bounced back to portal.
        // ─────────────────────────────────────────────────────────────
        $mtError = $request->input('error');
        if ($mtError && str_contains(strtolower($mtError), 'traffic limit')) {
            $mobile = $request->input('username') ?: session('mobile');
            $user   = $mobile ? WifiUser::where('mobile', $mobile)->first() : null;

            $activeSession = $user
                ? WifiSession::where('user_id', $user->id)
                    ->where('expires_at', '>', now())
                    ->whereNull('logout_at')
                    ->with('plan')
                    ->latest()
                    ->first()
                : null;

            $plan            = $activeSession?->plan;
            $hasActiveDailyPlan = $plan && $plan->plan_type === 'daily';

            $expiresIn = $activeSession
                ? \Carbon\Carbon::parse($activeSession->expires_at)->diffForHumans(['parts' => 1, 'short' => true])
                : null;

            return view('data-exhausted', compact('plan', 'hasActiveDailyPlan', 'expiresIn'));
        }

        // 💾 Always persist the router's link_login
        if ($linkLogin) {
            session(['link_login' => $linkLogin]);
        }

        // 🛡️ 15-DAY MAC LOGIC: 
        if ($mac) {
            $lastUser = WifiUser::where('mac_address', $mac)
                ->where('last_verified_at', '>', Carbon::now()->subDays(15))
                ->latest('last_verified_at')
                ->first();

            if ($lastUser) {
                // Found a recently verified device!
                session([
                    'mobile' => $lastUser->mobile,
                    'user_id' => $lastUser->id,
                    'user_name' => $lastUser->full_name,
                    'mac' => $mac,
                    'ip' => $ip,
                ]);

                // 📦 Check if they have an active plan
                $activeSession = WifiSession::where('user_id', $lastUser->id)
                    ->where('expires_at', '>', Carbon::now())
                    ->whereNull('logout_at')
                    ->latest()
                    ->first();

                if ($activeSession) {
                    Log::info("[AutoLogin] MAC $mac authorized via 15-day rule for " . $lastUser->mobile);
                    // They have a plan! Do the MikroTik handshake.
                    return $this->buildHotspotLoginForm($lastUser, $linkLogin ?: session('link_login'));
                } else {
                    Log::info("[AutoLogin] MAC $mac recognized (no active plan). Sending to Plans.");
                    // No plan, but recognized! Send them straight to selection.
                    return redirect('/plans');
                }
            }
        }

        return view('login', [
            'mac' => $mac,
            'ip' => $ip,
            'link_login' => $linkLogin,
        ]);
    }

    /**
     * Build an auto-submitting POST form to authorize the user on MikroTik.
     *
     * CRITICAL NOTES:
     * 1. MikroTik ONLY accepts POST for hotspot login — GET redirects are ignored.
     * 2. wifi.local is replaced with the router IP (192.168.88.1) because wifi.local
     *    may not resolve via DNS on all Android/iOS devices quickly enough.
     * 3. dst points to our /success page (in walled garden) — NOT google.com.
     *    google.com may be intercepted before the session activates, causing a loop.
     */
    private function buildHotspotLoginForm($user, $linkLogin)
    {
        // Replace hotspot DNS name with actual router IP for universal device compatibility
        $routerHost = env('MIKROTIK_HOST', '192.168.88.1');
        $hotspotIp  = env('MIKROTIK_HOTSPOT_IP', $routerHost);
        
        $loginUrl = str_replace(
            ['wifi.local', 'portal.wifi', 'mywifi.net', $routerHost], 
            $hotspotIp, 
            $linkLogin ?? "http://{$hotspotIp}/login"
        );
        $mac = session('mac') ?? $user->mac_address ?? '';

        return view('mikrotik-login', [
            'link_login' => $loginUrl,
            'username'   => $user->mobile,
            'password'   => $user->mobile,
            'mac'        => $mac,
            'dst'        => url('/success'),
        ]);
    }

    // send otp
    public function sendOtp(Request $request)
    {
        $request->validate(['mobile' => 'required|digits:10']);
        $mobile = $request->mobile;

        // Persist the environmental context
        session([
            'temp_mobile' => $mobile,
            'temp_mac' => $request->input('mac'),
            'temp_ip' => $request->input('ip'),
            'temp_link_login' => $request->input('link_login') ?: session('link_login')
        ]);

        return $this->processOtpRequest($request);
    }

    public function saveRegistration(Request $request)
    {
        // 🛡️ FORENSIC COMPLIANCE: Registering the full KYC details
        $request->validate([
            'mobile' => 'required',
            'full_name' => 'required',
            'address' => 'required',
            'pincode' => 'required',
            'consent' => 'accepted'  // Mandatory checkbox
        ]);

        $user = WifiUser::create([
            'mobile' => $request->mobile,
            'full_name' => $request->full_name,
            'address' => $request->address,
            'city' => $request->city,
            'district' => $request->district,
            'state' => $request->state,
            'pincode' => $request->pincode,
            'id_proof' => $request->id_proof, // Now optional
            'mac_address' => $request->mac,
            'ip_address' => $request->ip
        ]);

        // After saving, proceed to OTP
        return $this->processOtpRequest($request);
    }

    private function processOtpRequest(Request $request)
    {
        $mobile = $request->mobile;

        // Rate limit
        $recentOtp = OtpRequest::where('mobile', $mobile)
            ->where('created_at', '>', Carbon::now()->subMinutes(1))
            ->count();

        if ($recentOtp >= 3) {
            return back()->with('error', 'Too many OTP requests.');
        }

        $otp = rand(100000, 999999);
        OtpRequest::create([
            'mobile' => $mobile,
            'otp_code' => $otp,
            'ip_address' => $request->ip(),
            'expires_at' => Carbon::now()->addMinutes(5)
        ]);

        // echo "Your OTP is: " . $otp;
        return view('verify', [
            'mobile' => $mobile,
            'otp' => $otp,
            'mac' => $request->input('mac'),
            'ip' => $request->input('ip'),
            'link_login' => $request->input('link_login')
        ]);
    }

    // verify otp (Complete Integrated Handshake)
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'mobile' => 'required|digits:10',
            'otp' => 'required|digits:6',
        ]);

        $otpRequest = OtpRequest::where('mobile', $request->mobile)
            ->where('otp_code', $request->otp)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$otpRequest) {
            return back()->with('error', 'Invalid or expired OTP.');
        }

        // ✅ OTP IS VALID!
        $user = WifiUser::where('mobile', $request->mobile)->first();
        $mac = $request->input('mac') ?: session('temp_mac') ?: 'unknown';
        $ip = $request->input('ip') ?: session('temp_ip') ?: $request->ip();

        if (!$user) {
            // This shouldn't happen after verifyOtp if they came from registration, 
            // but just in case, we redirect to registration.
            return view('register', [
                'mobile' => $request->mobile,
                'mac' => $mac,
                'ip' => $ip,
                'link_login' => session('temp_link_login')
            ]);
        }

        // ✅ MARK AS VERIFIED
        $user->update([
            'mac_address' => $mac,
            'ip_address' => $ip,
            'last_verified_at' => Carbon::now()
        ]);

        // 💾 PERSIST SESSION STICKILY
        session([
            'mobile' => $user->mobile,
            'user_id' => $user->id,
            'user_name' => $user->full_name,
            'mac' => $mac,
            'ip' => $ip,
        ]);

        // Check if they have an active plan. 
        // 🔄 ROUTER RESET RECOVERY:
        // If they have a plan, we RE-PUSH the limits to MikroTik now.
        // This handles cases where the router rebooted and lost its memory.
        $activeSession = WifiSession::where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->whereNull('logout_at')
            ->latest()
            ->first();

        if ($activeSession) {
            try {
                $plan     = $activeSession->plan;
                $mikrotik = new \App\Services\MikrotikService();
                $profile  = $plan->profile_name ?: 'plan_' . $plan->id;
                
                $mbLimit = $plan->isDailyPlan() ? $plan->daily_data_mb : $plan->limit_bytes;
                $bytesLimit = $mbLimit > 0 ? ceil($mbLimit * 1.05) . 'M' : null;
                $uptimeLimit = $plan->duration_minutes . 'm';
                $rateLimit = ($plan->upload_limit && $plan->download_limit) ? "{$plan->upload_limit}/{$plan->download_limit}" : null;

                $mikrotik->addHotspotUser($user->mobile, $user->mobile, $profile, $uptimeLimit, $bytesLimit, $rateLimit, $mac);
                Log::info("[AuthRecovery] Re-pushed plan for $user->mobile after reboot login.");
            } catch (\Exception $e) {
                Log::warning("[AuthRecovery] Re-push failed: " . $e->getMessage());
            }

            return $this->buildHotspotLoginForm($user, session('temp_link_login') ?: session('link_login'));
        }

        return redirect('/plans');
    }

    // disconnect
    public function disconnect(Request $request)
    {
        $mac = $request->input('mac');
        if (!$mac) {
            return redirect('/login')->with('error', 'MAC address required.');
        }

        WifiSession::where('mac_address', $mac)
            ->whereNull('logout_at')
            ->update(['logout_at' => now()]);

        return redirect('/login?mac=' . $mac)->with('success', 'Disconnected.');
    }

    // Connection Dashboard — shown after MikroTik redirects to dst=/success
    public function success(Request $request)
    {
        $mobile = session('mobile');
        if (!$mobile) {
            return redirect('/login')->with('error', 'Session expired. Please login again.');
        }

        $user = WifiUser::where('mobile', $mobile)->first();
        if (!$user) {
            return redirect('/login')->with('error', 'User not found. Please login again.');
        }

        // Find the most recent active session for this user (by MAC or by user_id)
        $mac = session('mac') ?? $user->mac_address;
        $session = WifiSession::where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        return view('success', compact('user', 'session'));
    }

    // Step 19 redirect to Router (The Final Authorize)
    private function redirectToRouter($user, $request)
    {
        // 🚀 SMART DNS DETECTION: Prioritize wifi.local over IP
        $linkLogin = $request->input('link_login') ?? $request->input('link-login') ?? session('link_login') ?? 'http://wifi.local/login';

        $separator = str_contains($linkLogin, '?') ? '&' : '?';
        $loginUrl = $linkLogin . $separator . 'username=' . urlencode($user->mobile) . '&password=' . urlencode($user->mobile);
        return redirect($loginUrl);
    }

    // 🔬 DIAGNOSTIC TOOL: Test MikroTik Connection
    public function testMikrotik()
    {
        try {
            $mikrotik = new MikrotikService();
            $client = $mikrotik->connect();

            if ($client === true) {
                return "<h1>✅ MOCK MODE ACTIVE</h1><p>The system is in Development Mode (MIKROTIK_CONNECTED=false).</p>";
            }

            return "<h1>✅ SUCCESS!</h1><p>Your Laravel server is now talking to the MikroTik router successfully.</p>";
        } catch (\Exception $e) {
            return "<h1>❌ CONNECTION FAILED!</h1><p>Error: " . $e->getMessage() . "</p><p>Check your .env settings and IP -> Services -> API in Winbox.</p>";
        }
    }
}
