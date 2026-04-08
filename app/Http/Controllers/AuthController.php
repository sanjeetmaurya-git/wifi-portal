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
        $mac        = $request->input('mac') ?: $request->input('mac-address');
        $linkLogin  = $request->input('link_login') ?? $request->input('link-login');
        $ip         = $request->input('ip') ?: $request->input('ip-address') ?: $request->ip();

        // 💾 Always persist the router's link_login in the session from the very first hit
        if ($linkLogin) {
            session(['link_login' => $linkLogin]);
        }

        // 🔁 AUTO-LOGIN: If this MAC is verified within 15 days and has an active plan
        if ($mac) {
            $user = WifiUser::where('mac_address', $mac)
                ->where('last_verified_at', '>', Carbon::now()->subDays(15))
                ->first();

            if ($user) {
                $activeSession = WifiSession::where('user_id', $user->id)
                    ->where('expires_at', '>', Carbon::now())
                    ->whereNull('logout_at')
                    ->latest()
                    ->first();

                // 📦 QUEUED PLAN AUTO-ACTIVATION
                if (!$activeSession) {
                    $queuedSession = WifiSession::where('user_id', $user->id)
                        ->where('expires_at', '>', Carbon::now())
                        ->where('login_at', '>', Carbon::now()) // Starts in future
                        ->first();

                    if ($queuedSession) {
                        Log::info("[Queue] Activating queued plan for " . $user->mobile);
                        $queuedSession->update(['login_at' => now()]);
                        // Recalculate expiry
                        $queuedSession->update(['expires_at' => now()->addMinutes($queuedSession->duration_minutes)]);
                        $activeSession = $queuedSession;

                        // Push to MikroTik
                        $mikrotik = new \App\Services\MikrotikService();
                        $plan = $activeSession->plan;
                        if ($plan) {
                             $mikrotik->addHotspotUser(
                                $user->mobile,
                                $user->mobile,
                                $plan->profile_name ?: 'plan_' . $plan->id,
                                $plan->duration_minutes . 'm',
                                $plan->limit_bytes ? ($plan->limit_bytes . 'M') : null,
                                ($plan->upload_limit && $plan->download_limit) ? "{$plan->upload_limit}/{$plan->download_limit}" : null,
                                $mac
                            );
                        }
                    }
                }

                if ($activeSession) {
                    // ✅ User has an active plan in DB.
                    // Kick active session to ensure fresh login works
                    $this->mikrotik->removeActiveSession($user->mobile);

                    // Always persist session data.
                    $savedLinkLogin = $linkLogin ?: session('link_login');
                    session([
                        'mobile'     => $user->mobile,
                        'mac'        => $mac,
                        'ip'         => $ip,
                        'link_login' => $savedLinkLogin,
                    ]);

                    // 🔑 KEY LOGIC:
                    // If MikroTik redirected here (link_login is present in URL), the user
                    // is NOT yet authenticated on the router for this session.
                    if ($savedLinkLogin) {
                        return $this->buildHotspotLoginForm($user, $savedLinkLogin);
                    }

                    // No link_login = user browsed directly to /login (not from MikroTik).
                    // Just show the status/success page.
                    return redirect('/success');
                } else {
                    // ✅ User is verified but plan expired → skip login, go to plans
                    session([
                        'mobile'     => $user->mobile,
                        'mac'        => $mac,
                        'ip'         => $ip,
                        'link_login' => $linkLogin ?: session('link_login'),
                    ]);
                    return redirect('/plans');
                }
            }
        }

        // 🆕 New user or no MAC → Show login form
        return view('login', [
            'mac'        => $mac,
            'ip'         => $ip,
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
        $routerIp  = env('MIKROTIK_HOST', '192.168.88.1');
        $loginUrl  = str_replace('wifi.local', $routerIp, $linkLogin ?? "http://{$routerIp}/login");
        $mac       = session('mac') ?? $user->mac_address ?? '';

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
        $request->validate(['mobile' => 'required | digits:10']);
        $mobile = $request->mobile;

        // 🔥 KYC CHECK: If NEW User, redirect to Registration
        $userExists = WifiUser::where('mobile', $mobile)->exists();
        if (!$userExists) {
            return view('register', [
                'mobile' => $mobile,
                'mac' => $request->input('mac'),
                'ip' => $request->input('ip'),
                'link_login' => $request->input('link_login')
            ]);
        }

        // EXISTING USER: Proceed to OTP
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
    public function verifyOtp(Request $request, MikrotikService $mikrotik)
    {
        // 1️⃣ Validate request
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

        // 🔥 15-DAY LOGIC: Update verification status
        $user = WifiUser::where('mobile', $request->mobile)->first();
        $mac = $request->input('mac') ?: $request->input('mac-address') ?: 'unknown';
        $ip = $request->input('ip') ?: $request->ip();

        if ($user) {
            $user->update([
                'mac_address' => $mac,
                'ip_address' => $ip,
                'last_verified_at' => Carbon::now()
            ]);
        }

        // 🛡️ DEVICE INFO
        $agent = $request->header('User-Agent');
        $browser = str_contains($agent, 'Chrome') ? 'Chrome' : 'Unknown';
        $os = str_contains($agent, 'Android') ? 'Android' : 'Unknown';

        // 7️⃣ PREPARE FOR PLAN SELECTION: Store session data and redirect
        session([
            'mobile' => $user->mobile,
            'mac' => $mac,
            'ip' => $ip,
            'link_login' => $request->input('link_login')
        ]);

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
