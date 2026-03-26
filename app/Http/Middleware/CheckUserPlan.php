<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\WifiSession;

use function Symfony\Component\Clock\now;

class CheckUserPlan
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $mobile = session('mobile');

        if (!$mobile) {
            return redirect('/login');
        }

        $session = WifiSession::whereHas('user', function ($q) use ($mobile) {
            $q->where('mobile', $mobile);
        })
        ->whereNull('logout_at')
        ->latest()
        ->first();

        // ❌ No active session
        if (!$session) {
            return redirect('/plans')->with('error', 'No Active Plan');
        }

        // ⏱ Check expiry
        $expiry = \Carbon\Carbon::parse($session->login_at)->addMinutes($session->duration_minutes);

        if (now() > $expiry){
            // mark logout
            $session->update(['logout_at' => now()]);

            return redirect('/plans')->with('error', 'Your Plan Expired');
        }

        return $next($request);
    }
}
