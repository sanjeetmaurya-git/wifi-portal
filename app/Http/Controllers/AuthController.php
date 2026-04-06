<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OtpRequest;
use Carbon\Carbon;
use App\Models\WifiUser;
use App\Models\WifiSession;
use App\Services\MikrotikService;
use App\Models\WifiPlan;

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

        // 🔥 15-DAY INTELLIGENCE: Check if user exists and was verified in last 15 days
        $user = WifiUser::where('mac_address', $mac)
            ->where('last_verified_at', '>', Carbon::now()->subDays(15))
            ->first();

        if ($user) {
            // User is verified! Now check for an active plan session
            $activeSession = WifiSession::where('user_id', $user->id)
                ->where('mac_address', $mac)
                ->where('expires_at', '>', Carbon::now())
                ->latest()
                ->first();

            if ($activeSession) {
                // Instantly Auto-Authorize on MikroTik
                return $this->redirectToRouter($user, $request);
            } else {
                // Verified but no active plan -> Show Plans
                $plans = WifiPlan::where('is_active', true)->get();
                return view('plans', [
                    'plans' => $plans,
                    'mobile' => $user->mobile,
                    'mac' => $mac,
                    'ip' => $request->ip(),
                    'link_login' => $linkLogin
                ]);
            }
        }

        // NO SESSION OR EXPIRED: Show Login form
        return view('login', [
            'mac' => $mac,
            'ip' => $request->ip(),
            'link_login' => $linkLogin
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

        // 6️⃣ Give FREE Trial if eligible
        $usedFreeThisMonth = WifiSession::where('user_id', $user->id)
            ->where('is_free', true)
            ->whereMonth('login_at', now()->format('m'))
            ->exists();

        if (!$usedFreeThisMonth) {
            $plan = \App\Models\WifiPlan::where('name', 'Free Plan')->where('is_active', true)->first();
            $duration = $plan ? $plan->duration_minutes : 30;

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

        // 7️⃣ Stop the Infinite Reload Loop — Add User to Router + Handshake Redirect
        try {
            $mikrotik->addHotspotUser($user->mobile, $user->mobile, 'default', null);
        } catch (\Exception $e) { /* Already exists */
        }

        return $this->redirectToRouter($user, $request);
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

    // Step 19 redirect to Router (The Final Authorize)
    private function redirectToRouter($user, $request)
    {
        $linkLogin = $request->input('link_login') ?? $request->input('link-login') ?? 'http://' . env('MIKROTIK_HOST', '192.168.88.1') . '/login';
        $separator = str_contains($linkLogin, '?') ? '&' : '?';
        $loginUrl = $linkLogin . $separator . 'username=' . urlencode($user->mobile) . '&password=' . urlencode($user->mobile);
        return redirect($loginUrl);
    }
}
