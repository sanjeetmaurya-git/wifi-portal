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
    public function index(RadiusService $radius, \App\Services\MikrotikService $mikrotik)
    {
        $mobile = session('mobile');
        if (!$mobile) {
            return redirect('/login')->with('error', 'Session expired. Please login again.');
        }

        $user = WifiUser::where('mobile', $mobile)->first();
        if (!$user) {
            return redirect('/login');
        }

        // ── LIVE REAL-TIME SYNC ─────────────────────────────────────────────
        // Fetch current active plan
        $activeSession = WifiSession::where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->whereNull('logout_at')
            ->latest()
            ->first();

        // Try to get live data from MikroTik for THIS specific user
        if ($activeSession) {
            try {
                $mtActive = $mikrotik->getActiveUsers(); // Get all active users
                $userEntry = collect($mtActive)->firstWhere('user', $mobile);
                
                if ($userEntry) {
                    $bytesTotal = ((int) ($userEntry['bytes-in'] ?? 0)) + ((int) ($userEntry['bytes-out'] ?? 0));
                    $liveMb = round($bytesTotal / 1048576, 2); // 1024 * 1024
                    
                    // Update the session record so the page shows FRESH data
                    $activeSession->update(['used_mb' => $liveMb]);
                    $activeSession->refresh(); // Load the new value
                }
            } catch (\Exception $e) {
                Log::warning("[UsageDashboard] Could not sync live data from MikroTik: " . $e->getMessage());
            }
        }

        // Fetch historical usage data from FreeRADIUS (as backup/history)
        $usage = $radius->getUsage($mobile);

        return view('user.usage', [
            'user' => $user,
            'usage' => $usage,
            'activeSession' => $activeSession,
            'plan' => $activeSession ? $activeSession->plan : null
        ]);
    }
}
