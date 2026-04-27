<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\WifiSession;

class UserController extends Controller
{
    //Step 24 user see their plan history 
    public function myPlans(){
        $mobile = session('mobile');
        if(!$mobile){
            return redirect('/login');
        }

        $transactions = Transaction::with(['plan', 'user'])
        ->whereHas('user', function ($q) use ($mobile) {
            $q->where('mobile', $mobile);
        })
        ->where('status', 'paid')
        ->latest()
        ->get();

        $sessions = WifiSession::whereHas('user', function ($q) use ($mobile){
            $q->where('mobile', $mobile);
        })->latest()->get();

        return view('user.my_plans', compact('transactions','sessions') );
    }

    // Profile Page (Shows KYC and links)
    public function profile()
    {
        $mobile = session('mobile');
        if (!$mobile) return redirect('/login');

        $user = \App\Models\WifiUser::where('mobile', $mobile)->first();
        
        // 📡 Fetch Data Usage & Session Status
        $radius = new \App\Services\RadiusService();
        $usage  = $radius->getUsage($mobile); 
        // Note: Also check usage by MAC if available
        if ($user->mac_address) {
            $usage += $radius->getUsage($user->mac_address);
        }

        $session = \App\Models\WifiSession::where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->whereNull('logout_at')
            ->with('plan')
            ->latest()
            ->first();

        return view('user.profile', compact('user', 'usage', 'session'));
    }

    // Add another number (triggers OTP for the new number)
    public function addSecondaryNumber(Request $request)
    {
        $request->validate(['secondary_mobile' => 'required|digits:10']);
        
        // Prepare a new session for the secondary number verification
        session(['temp_mobile' => $request->secondary_mobile]);

        // Redirect to AuthController to handle OTP sending
        return redirect()->action([\App\Http\Controllers\AuthController::class, 'sendOtp'], ['mobile' => $request->secondary_mobile]);
    }

    //Step 25 
    public function checkSession()
    {
        $mobile = session('mobile');

        if (!$mobile) {
            return response()->json(['active' => false]);
        }

        $session = \App\Models\WifiSession::whereHas('user', function ($q) use ($mobile) {
            $q->where('mobile', $mobile);
        })
        ->whereNull('logout_at')
        ->latest()
        ->first();

        if (!$session) {
            return response()->json(['active' => false]);
        }

        $expiry = \Carbon\Carbon::parse($session->login_at)
        ->addMinutes($session->duration_minutes);

        if (now() > $expiry) {
            $session->update(['logout_at' => now()]);
            return response()->json(['active' => false]);
        }

        return response()->json(['active' => true]);
    }
}
