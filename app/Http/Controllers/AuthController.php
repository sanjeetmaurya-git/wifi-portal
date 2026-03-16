<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OtpRequest;
use Carbon\Carbon;
use App\Models\WifiUser;
use App\Models\WifiSession;
use App\Services\MikrotikService;

class AuthController extends Controller
{
    // login page
    public function loginPage(Request $request)
    {
        $mac = $request->input('mac', 'unknown');

        // Check if this MAC has an active, non-expired session
        $activeSession = WifiSession::where('mac_address', $mac)
                                    ->where('expires_at', '>', Carbon::now())
                                    ->latest()
                                    ->first();

        if ($activeSession) {
            return view('status', [
                'session' => $activeSession,
                'mobile'  => $activeSession->user->mobile ?? 'User',
            ]);
        }

        return view('login');
    }

    // send otp
    public function sendOtp(Request $request)
    {
        // Validate mobile
        $request->validate(['mobile' => 'required|digits:10']);

        $mobile = $request->mobile;
        $otp = rand(100000, 999999);

        // save otp in database
        OtpRequest::create([
            'mobile'     => $mobile,
            'otp_code'   => $otp,
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
            'mobile'     => $mobile,
            'mac'        => $request->input('mac'),
            'ip'         => $request->input('ip') ?? $request->ip(),
            'link_login' => $request->input('link_login')
        ]);
    }

    // verify otp
    public function verifyOtp(Request $request, MikrotikService $mikrotik)
    {
        // Validate request
        $request->validate([
            'mobile' => 'required|digits:10',
            'otp'    => 'required|digits:6',
        ]);

        // Find valid OTP
        $otpRecord = OtpRequest::where('mobile',     $request->mobile)
                                ->where('otp_code',  $request->otp)
                                ->where('verified',  false)
                                ->where('expires_at', '>', Carbon::now())
                                ->first();
        
        // OTP invalid or expired — redirect back with error
        if (!$otpRecord) {
            return redirect()->back()
                ->withInput($request->only('mobile', 'mac', 'ip', 'link_login'))
                ->with('error', 'Invalid or Expired OTP. Please try again.');
        }

        // Mark OTP as verified
        $otpRecord->update(['verified' => true]);

        // Create or get WiFi user
        $user = WifiUser::firstOrCreate([
            'mobile' => $request->mobile
        ]);

        // Read MAC and IP from hidden form fields (sent by router)
        $mac = $request->input('mac') ?: '00:00:00:00:00:00';
        $ip  = $request->input('ip')  ?: $request->ip();

        // Capture device info from User-Agent header
        $agent = $request->header('User-Agent');

        //detect browser
        $browser = 'Unknown';
        if(str_contains($agent,'Chrome')) {
            $browser = 'Chrome';
        } elseif (str_contains($agent,'Firefox')) {
            $browser = 'Firefox';
        } 
        elseif (str_contains($agent,'Safari')) {
            $browser = 'Safari';
        }

        $os = 'Unknown';
        if (str_contains($agent,'Windows')) {
            $os = 'Windows';
        } elseif (str_contains($agent,'Android')) {
            $os = 'Android';
        } elseif (str_contains($agent,'Linux')) {
            $os = 'Linux';
        } elseif (str_contains($agent,'iPhone')) {
            $os = 'iOS';
        }
        // Create WiFi session
        try {
            // 🔥 STEP 18: Get Default Free Plan
            $plan = \App\Models\WifiPlan::where('name', 'Free Plan')->where('is_active', true)->first();
            
            // Fallback if seeder hasn't run
            $duration = $plan ? $plan->duration_minutes : 30;
            $rateLimit = $plan ? ($plan->upload_limit . '/' . $plan->download_limit) : null;

            \App\Models\WifiSession::create([
                'user_id'          => $user->id,
                'wifi_plan_id'     => $plan ? $plan->id : null,
                'mac_address'      => $mac,
                'ip_address'       => $ip,
                'device_name'      => $agent,
                'browser'          => $browser,
                'os'               => $os,
                'login_at'         => Carbon::now(),
                'expires_at'       => Carbon::now()->addMinutes($duration),
                'duration_minutes' => $duration,
            ]);
        } catch (\Throwable $e) {
            // ... (rest of catch remains same)
            logger()->error("WifiSession creation failed: " . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'data' => [
                    'user_id' => $user->id,
                    'mac' => $mac,
                    'ip' => $ip,
                ]
            ]);
            throw $e;
        }
        
        // Sync user with router
        $routerSynced = false;

        try {
            $routerSynced = $mikrotik->addHotspotUser(
                $request->mobile,
                $request->otp,
                'default',
                $rateLimit ?? null
            );
        } catch (\Exception $e) {
            // router not connected yet
        }

        // 🔥 STEP 15 Router Redirect
        $linkLogin = $request->input('link_login');
        if ($linkLogin) {
            $loginUrl = $linkLogin .
                "?username=" . $request->mobile .
                "&password=" . $request->otp;
            return redirect($loginUrl);
        }

        // Redirect to success page
        // If router sent a link_login, pass it to success view for captive portal redirect
        return view('success', [
            'mobile'       => $request->mobile,
            'link_login'   => $request->input('link_login'),
            'routerSynced' => $routerSynced,
        ]);
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
                'logout_at'  => Carbon::now(),
                'expires_at' => Carbon::now(), // expire immediately
            ]);
        }

        return redirect('/hotspot/login?mac=' . $mac)->with('success', 'Disconnected successfully.');
    }
}
