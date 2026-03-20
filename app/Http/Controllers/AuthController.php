<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OtpRequest;
use Carbon\Carbon;
use App\Models\WifiUser;
use App\Models\WifiSession;
use App\Services\MikrotikService;

// // use function Symfony\Component\Clock\now;

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
            'otp'        => $otp, // Pass OTP for development
            'mac'        => $request->input('mac'),
            'ip'         => $request->input('ip') ?? $request->ip(),
            'link_login' => $request->input('link_login')
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
            'otp'    => 'required|digits:6',
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

        // 5️⃣ Get MAC + IP
        $mac = $request->input('mac') ?: '00:00:00:00:00:00';
        $ip  = $request->input('ip')  ?: $request->ip();

        // ✅ Store in session for PaymentController
        session([
            'mobile' => $request->mobile,
            'mac'    => $mac,
            'ip'     => $ip
        ]);

        // Capture device info from User-Agent header
        $agent = $request->header('User-Agent');

        // detect browser
        $browser = 'Unknown';
        if(str_contains($agent,'Chrome')) {
            $browser = 'Chrome';
        } elseif (str_contains($agent,'Firefox')) {
            $browser = 'Firefox';
        } elseif (str_contains($agent, 'Safari') && !str_contains($agent, 'Chrome')) {
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

        // 6️⃣ Router sync (ONLY create user in router, NOT session)
        try {
            $mikrotik->addHotspotUser(
                $request->mobile,
                $request->mobile, // password same as mobile (or change later)
                'default',
                null
            );
        } catch (\Exception $e) {
            // Ignore if router not connected
        }

        // 🔥 7️⃣ MAIN LOGIC ENTRY (MOST IMPORTANT)
        // return $this->handleUserAccess($user, $mac, $ip);
        return $this->handleUserAccess($user, $mac, $ip, $agent, $browser, $os);
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

    // Step 19 HotSpot Login 
    public function hotspotLogin(Request $request)
    {
        $mac = $request->mac;
        $ip  = $request->ip;

        // Step 1: Find existing session by MAC
        $session = WifiSession::where('mac_address', $mac)
        ->latest()
        ->first();

        if(!$session){
            return view('login', compact('mac','ip'));
        }

        $user = $session->user;

        // Step 2: Check 15 days rule

        if(!$user->last_verified_at || $user->last_verified_at->diffInDays(now()) > 15){
            return view('login', compact('mac','ip'));
        }

        // Step 3: Check active session
        // $isActive = now()->diffInMinutes($session->login_at) < $session->duration_minutes;
        $isActive = $session->expires_at > now();

        if($isActive){

            // AUTO LOGIN
            return $this->redirectToRouter($user, $request);
        }

        // Step 4: Plan expired → show plans
        return view('plans', compact('mac','ip'));
    }

    //Step 19 redirect to Login page 
    private function redirectToRouter($user, $request)
    {
        $link = $request->link_login;
        if(!$link){
            return "Connected (Dev Mode)";
        }

        return redirect($link . "?username=".$user->mobile. "&password=".$user->mobile);
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
                'mobile'       => $user->mobile,
                'link_login'   => request()->input('link_login'),
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
                'device_name'      => $agent ?? 'Unknown',
                'browser'          => $browser,
                'os'               => $os,
            ]);

            // return redirect('/success');
            return view('success', [
                'mobile'       => $user->mobile,
                'link_login'   => request()->input('link_login'),
                'routerSynced' => false,
            ]);
        }

        // 3️⃣ No plan → redirect to plans
        return redirect('/plans');
    }


}
