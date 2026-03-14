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
    public function loginPage()
    {
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
            'mobile'=>$mobile,
            'mac'=>$request->mac,
            'ip'=>$request->ip,
            'link_login'=>$request->link_login            
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

        // Create WiFi session
        // Read MAC and IP from hidden form fields (sent by router)
        $mac = $request->input('mac', 'unknown');
        $ip  = $request->input('ip',  $request->ip());  // fallback to server IP if not provided

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
        WifiSession::create([
            'user_id'          => $user->id,
            'mac_address'      => $mac,
            'ip_address'       => $ip,
            'device_name'      => $agent,
            'browser'          => $browser,
            'os'               => $os,
            'login_at'         => Carbon::now(),
            'duration_minutes' => 30,
        ]);
        
        // Sync user with router
        $routerSynced = false;

        try {
            $routerSynced = $mikrotik->addHotspotUser(
                $request->mobile,
                $request->otp
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
}
