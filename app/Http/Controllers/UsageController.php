<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RadiusService;
use App\Models\WifiUser;
use App\Models\WifiSession;
use Illuminate\Support\Facades\Log;

class UsageController extends Controller
{
    /**
     * Show the detailed usage dashboard for the user.
     */
    public function index(RadiusService $radius)
    {
        $mobile = session('mobile');
        if (!$mobile) {
            return redirect('/login')->with('error', 'Session expired. Please login again.');
        }

        $user = WifiUser::where('mobile', $mobile)->first();
        if (!$user) {
            return redirect('/login');
        }

        // Fetch usage data from FreeRADIUS tables
        $usage = $radius->getUsage($mobile);
        
        // Find current active plan
        $activeSession = WifiSession::where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->whereNull('logout_at')
            ->latest()
            ->first();

        // If no active session, they might be in a grace period or viewing history
        return view('user.usage', [
            'user' => $user,
            'usage' => $usage,
            'activeSession' => $activeSession,
            'plan' => $activeSession ? $activeSession->plan : null
        ]);
    }
}
