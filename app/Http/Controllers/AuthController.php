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

        return view('verify', compact('mobile'));
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

        // OTP invalid or expired — early return
        if (!$otpRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or Expired OTP.',
            ], 422);
        }

        // Mark OTP as verified
        $otpRecord->update(['verified' => true]);

        // Create or get WiFi user
        $user = WifiUser::firstOrCreate([
            'mobile' => $request->mobile
        ]);

        // Create WiFi session
        $mac = $request->mac ?? 'unknown';
        $ip = $request->ip();
        WifiSession::create([
            'user_id'     => $user->id,
            'mac_address' => $mac,       // will be replaced by router MAC later
            'ip_address'  => $ip,
            'login_at'    => Carbon::now(),
            'duration_minutes'=>30, //default login session
        ]);

        // Add user to MikroTik Router
        $routerSynced = false;

        try {
            $routerSynced = $mikrotik->addHotspotUser(
                $request->mobile,
                $request->otp
            );
        } catch (\Exception $e) {
            // router not connected yet
        }

        // return "Login Success";

        return response()->json([
            'success' => true,
            'message' => 'Internet Access Granted',
            'router'  => $routerSynced ? 'synced' : 'pending',
        ], 200);
        
        // if($request->otp == session('otp')){
        //     return "Login Success";
        // }else{
        //     return "Invalid Otp";
        // }
    }
}
