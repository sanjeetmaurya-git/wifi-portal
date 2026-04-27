<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;
use App\Models\Transaction;
use App\Models\WifiPlan;
use App\Models\WifiUser;
use App\Models\WifiSession;
use App\Services\MikrotikService;
use App\Services\RadiusService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    // ✅ Create Order (POST /create-order)
    public function createOrder(Request $request)
    {
        if (!session('mobile')) {
            return redirect('/login')->with('error', 'Session expired. Please login again.');
        }

        $plan = WifiPlan::findOrFail($request->plan_id);
        $user = WifiUser::where('mobile', session('mobile'))->first();

        if (!$user) {
            return redirect('/login')->with('error', 'User not found. Please login again.');
        }

        // 🆓 FREE PLAN LOGIC
        if ($plan->is_free) {
            // Strictly: one mobile number can claim one free plan ONLY ONCE
            $alreadyUsed = WifiSession::where('user_id', $user->id)
                ->where('is_free', true)
                ->exists();

            if ($alreadyUsed) {
                return redirect('/plans')->with('error', 'You have already used your free plan. Please purchase a plan to continue.');
            }

            return $this->activatePlan($user, $plan, $request, true);
        }

        // 🛠️ TEMPORARY TESTING MODE: Skipping Razorpay
        // This will directly activate the plan as if the payment was successful.
        \Illuminate\Support\Facades\Log::info("[TestMode] Bypassing payment for User: {$user->mobile} and Plan: {$plan->name}");

        return $this->activatePlan($user, $plan, $request, false);
    }

    // ✅ Payment Success Callback from Razorpay JS (AJAX POST)
    public function paymentSuccess(Request $request, MikrotikService $mikrotik)
    {
        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));

        try {
            $razorpayOrderId = $request->input('razorpay_order_id');
            $razorpayPaymentId = $request->input('razorpay_payment_id');
            $razorpaySignature = $request->input('razorpay_signature');

            if (!$razorpaySignature) {
                return response()->json(['error' => 'Payment signature missing.'], 400);
            }

            // 🔐 Verify payment signature with Razorpay
            $api->utility->verifyPaymentSignature([
                'razorpay_order_id' => $razorpayOrderId,
                'razorpay_payment_id' => $razorpayPaymentId,
                'razorpay_signature' => $razorpaySignature
            ]);

            // Find the transaction
            $transaction = Transaction::where('order_id', $razorpayOrderId)->first();

            if (!$transaction) {
                return response()->json(['error' => 'Transaction not found.'], 400);
            }

            // Prevent duplicate processing
            if ($transaction->status === 'paid') {
                $user = WifiUser::find($transaction->user_id);
                return $this->buildHandshakeResponse($user, $request);
            }

            $user = WifiUser::find($transaction->user_id);
            $plan = WifiPlan::find($transaction->wifi_plan_id);

            if (!$user || !$plan) {
                return response()->json(['error' => 'User or Plan not found.'], 400);
            }

            // 🔄 SESSION RECOVERY: If session was lost, log the user back in!
            if (!session('mobile')) {
                session([
                    'mobile' => $user->mobile,
                    'user_id' => $user->id,
                    'mac' => $user->mac_address ?? session('activate_mac'),
                    'link_login' => session('link_login') ?? "http://" . env('MIKROTIK_HOST', '192.168.88.1') . "/login"
                ]);
                Log::info("[SessionRecovery] User {$user->mobile} recovered after payment success.");
            }

            // ✅ Mark transaction as paid
            $transaction->update([
                'payment_id' => $razorpayPaymentId,
                'status' => 'paid',
                'expires_at' => now()->addMinutes($plan->duration_minutes),
            ]);

            // ✅ Activate the plan
            return $this->activatePlan($user, $plan, $request, false);

        } catch (SignatureVerificationError $e) {
            Log::error('[Payment] Signature verification failed: ' . $e->getMessage());
            return response()->json(['error' => 'Payment verification failed. Contact support.'], 400);
        } catch (\Exception $e) {
            Log::error('[Payment] Exception: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Central method to activate a plan (free or paid).
     *
     * PLAN TYPE RULES:
     * - daily/unlimited: Queue if same plan type is already active. Never run two daily or two unlimited together.
     * - datapack: Always activate immediately, stacks on top of the active daily plan by boosting its byte limit.
     */
    private function activatePlan($user, $plan, $request, bool $isFree)
    {
        $mac = session('mac') ?? $user->mac_address ?? 'unknown';
        $ip = session('ip') ?? $user->ip_address ?? $request->ip();

        // ── DATA PACK: activates immediately, standalone or on top of any plan ──
        if ($plan->isDataPack()) {
            // Find any currently active session (daily OR unlimited) to link as parent
            $activeParent = WifiSession::where('user_id', $user->id)
                ->where('expires_at', '>', now())
                ->whereNull('logout_at')
                ->latest()
                ->first();

            // Create the datapack session (linked to parent if one exists)
            $expiresAt = $activeParent
                ? $activeParent->expires_at   // data pack expires when current plan ends
                : now()->addMinutes($plan->duration_minutes > 0 ? $plan->duration_minutes : 43200);

            WifiSession::create([
                'user_id' => $user->id,
                'mac_address' => $mac,
                'ip_address' => $ip,
                'login_at' => now(),
                'duration_minutes' => $plan->duration_minutes,
                'expires_at' => $expiresAt,
                'wifi_plan_id' => $plan->id,
                'is_free' => $isFree,
                'parent_session_id' => $activeParent?->id,
                'bonus_data_mb' => $plan->limit_bytes ?? 0,
            ]);

            // Push to MikroTik: sum up base plan + ALL active data packs
            try {
                $mikrotik = new MikrotikService();
                $profileName = $plan->profile_name ?: 'default';
                // $packMb      = (int) ($plan->limit_bytes ?? 0);

                // 1. Get Base MB from the main active plan (daily/unlimited)
                $baseMb = 0;
                if ($activeParent && $activeParent->plan) {
                    $baseMb = (int) ($activeParent->plan->daily_data_mb
                        ?? $activeParent->plan->limit_bytes
                        ?? 0);
                }

                // 2. Sum up ALL active data packs for this user
                $totalBonusMb = (int) \App\Models\WifiSession::where('user_id', $user->id)
                    ->where('expires_at', '>', now())
                    ->whereNull('logout_at')
                    ->whereHas('plan', fn($q) => $q->where('plan_type', 'datapack'))
                    ->sum('bonus_data_mb');

                $totalMb = $baseMb + $totalBonusMb;

                $bytesLimit = $totalMb > 0 ? $totalMb . 'M' : null;
                $uptimeLimit = $plan->duration_minutes > 0 ? $plan->duration_minutes . 'm' : null;
                $rateLimit = ($plan->upload_limit && $plan->download_limit)
                    ? "{$plan->upload_limit}/{$plan->download_limit}"
                    : null;

                $mikrotik->addHotspotUser(
                    $user->mobile,
                    $user->mobile,
                    $profileName,
                    $uptimeLimit,
                    $bytesLimit,
                    $rateLimit,
                    $mac
                );
                Log::info("[DataPack] Activated for {$user->mobile} → Total Limit: {$totalMb}MB (Base: {$baseMb}MB + Total Bonus: {$totalBonusMb}MB)");
            } catch (\Exception $e) {
                Log::error("[DataPack] MikroTik push failed for {$user->mobile}: " . $e->getMessage());
            }


            session(['mobile' => $user->mobile]);
            return $this->buildHandshakeResponse($user, $request);
        }


        // ── DAILY / UNLIMITED: Smart Activation vs. Queuing ─────────
        $activeSession = WifiSession::where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->whereNull('logout_at')
            ->whereHas('plan', fn($q) => $q->where('plan_type', $plan->plan_type))
            ->latest()->first();

        // 🔄 SMART REACTIVATION: 
        // If current plan is >95% exhausted, we skip the queue and activate the new plan IMMEDIATELY.
        $isExhausted = false;
        if ($activeSession && $activeSession->plan) {
            $limit = $activeSession->plan->isDailyPlan() ? $activeSession->plan->daily_data_mb : $activeSession->plan->limit_bytes;
            if ($limit > 0 && ($activeSession->used_mb >= ($limit * 0.95))) {
                $isExhausted = true;
                // Terminate the old exhausted session
                $activeSession->update(['logout_at' => now(), 'notes' => 'Terminated early for recharge']);
                Log::info("[Plan] Terminating exhausted session for {$user->mobile} to activate new plan.");
            }
        }

        if ($activeSession && !$isExhausted) {
            // Queue the new plan to start after the current one expires
            $startAt = $activeSession->expires_at;
            WifiSession::create([
                'user_id' => $user->id,
                'mac_address' => $mac,
                'ip_address' => $ip,
                'login_at' => $startAt,
                'duration_minutes' => $plan->duration_minutes,
                'expires_at' => Carbon::parse($startAt)->addMinutes($plan->duration_minutes),
                'wifi_plan_id' => $plan->id,
                'is_free' => $isFree,
            ]);
            Log::info('[Plan] Queued ' . $plan->plan_type . ' plan for ' . $user->mobile . '. Starts at: ' . $startAt);

            session(['mobile' => $user->mobile]);
            return ($request->expectsJson() || $request->isXmlHttpRequest())
                ? response()->json(['redirect' => url('/success?queued=1')])
                : redirect('/success?queued=1');
        }

        // ── Activate immediately ───────────────
        WifiSession::create([
            'user_id' => $user->id,
            'mac_address' => $mac,
            'ip_address' => $ip,
            'login_at' => now(),
            'duration_minutes' => $plan->duration_minutes,
            'expires_at' => now()->addMinutes($plan->duration_minutes),
            'wifi_plan_id' => $plan->id,
            'is_free' => $isFree,
        ]);

        // ── Push limits to MikroTik with 5% Buffer ──────────────────────────
        try {
            $mikrotik = new MikrotikService();
            $profileName = $plan->profile_name ?: 'plan_' . $plan->id;

            $mbLimit = 0;
            if ($plan->isDailyPlan() && $plan->daily_data_mb) {
                $mbLimit = $plan->daily_data_mb;
            } elseif ($plan->limit_bytes) {
                $mbLimit = $plan->limit_bytes;
            }

            // 🎁 ADD 5% OVERHEAD BUFFER
            // If user pays for 500MB, we give 525MB on the router to account for network headers.
            $bytesLimit = $mbLimit > 0 ? ceil($mbLimit * 1.05) . 'M' : null;

            $uptimeLimit = $plan->duration_minutes . 'm';
            $rateLimit = ($plan->upload_limit && $plan->download_limit)
                ? "{$plan->upload_limit}/{$plan->download_limit}"
                : null;

            // ⚡ RADIUS SYNC: Sync both Mobile and MAC
            $radius = new RadiusService();
            $radius->syncUser($user->mobile, $user->mobile, $plan);
            $radius->syncMac($mac, $plan);

            Log::info("[RADIUS] Plan synced for $user->mobile and MAC $mac");
        } catch (\Exception $e) {
            Log::error('[RADIUS] Sync failed: ' . $e->getMessage());
        }

        return $this->buildHandshakeResponse($user, $request);
    }

    /**
     * Build the final authorization response.
     *
     * KEY FIX: For Razorpay (AJAX) requests we NO LONGER inject HTML via
     * document.write(). That approach is unreliable — window.onload does not
     * fire properly on dynamically written pages in many mobile browsers.
     *
     * Instead we store the connection data in the session and return a redirect
     * URL. The JS in payment.blade.php does window.location.href = url, which
     * causes a FULL page navigation to /activate-internet. That page renders
     * mikrotik-login.blade.php cleanly, and the IIFE auto-submits reliably.
     */
    private function buildHandshakeResponse($user, $request)
    {
        $rawLinkLogin = session('link_login');
        $mac = session('mac') ?? $user->mac_address ?? '';
        $mobile = $user->mobile;

        session(['mobile' => $mobile]);

        // ── Normalise the router URL ──────────────────────────────────────────
        $routerIp = env('MIKROTIK_HOST', '192.168.88.1');
        $linkLogin = $rawLinkLogin
            ? str_replace(['wifi.local', 'wifi.login', 'mywifi.net', 'portal.wifi'], $routerIp, $rawLinkLogin)
            : null;

        $hasLinkLogin = !empty($linkLogin) && str_contains($linkLogin, 'http');

        // ── AJAX response (Razorpay JS callback) ─────────────────────────────
        if ($request->expectsJson() || $request->isXmlHttpRequest()) {
            if ($hasLinkLogin) {
                session(['activate_link_login' => $linkLogin, 'activate_mac' => $mac]);
                return response()->json(['redirect' => url('/activate-internet')]);
            }
            return response()->json(['redirect' => url('/success')]);
        }

        // ── Regular browser request ───────────────────────────────────────────
        // If we have the router URL: show connecting screen (12s wait → GET login)
        if ($hasLinkLogin) {
            return view('mikrotik-login', [
                'link_login' => $linkLogin,
                'username' => $mobile,
                'password' => $mobile,
                'mac' => $mac,
                'dst' => url('/success'),
            ]);
        }

        // No router URL (user opened site directly) → just go to success
        return redirect('/success');
    }

    // ✅ GET /activate-internet — Shows connecting screen → GET login to router
    public function activateInternet(Request $request)
    {
        // 🔍 SESSION RECOVERY
        $mobile = session('mobile');
        $mac = session('activate_mac') ?? session('mac') ?? '';
        $linkLogin = session('activate_link_login') ?? session('link_login');

        if (!$mobile) {
            $user = WifiUser::where('mac_address', $mac)
                ->orWhere('ip_address', $request->ip())
                ->latest()
                ->first();

            if ($user) {
                $mobile = $user->mobile;
                session(['mobile' => $mobile, 'user_id' => $user->id, 'mac' => $user->mac_address]);
            } else {
                return redirect('/login')->with('error', 'Please login to finish connection.');
            }
        } else {
            $user = WifiUser::where('mobile', $mobile)->first();
        }

        if (!$user)
            return redirect('/login');

        // Re-enqueue the activation command for the router
        try {
            $mikrotik = new MikrotikService();
            $activeSession = WifiSession::where('user_id', $user->id)
                ->where('expires_at', '>', now())
                ->whereNull('logout_at')
                ->latest()->first();

            if ($activeSession && $activeSession->plan) {
                $plan = $activeSession->plan;
                $mbLimit = $plan->isDailyPlan() ? $plan->daily_data_mb : $plan->limit_bytes;
                $bytesLimit = $mbLimit > 0 ? ceil($mbLimit * 1.05) . 'M' : null;

                // ⚡ RADIUS RE-SYNC
                $radius = new RadiusService();
                $radius->syncUser($user->mobile, $user->mobile, $plan);
                $radius->syncMac($mac, $plan);
            }

        } catch (\Exception $e) {
            Log::warning("[FixInternet] Sync failed: " . $e->getMessage());
        }

        // If we have the router's URL, show the 12-second connecting screen
        $routerIp = env('MIKROTIK_HOST', '192.168.88.1');
        $cleanLink = $linkLogin
            ? str_replace(['wifi.local', 'wifi.login', 'mywifi.net', 'portal.wifi'], $routerIp, $linkLogin)
            : null;

        if (!empty($cleanLink) && str_contains($cleanLink, 'http')) {
            return view('mikrotik-login', [
                'link_login' => $cleanLink,
                'username' => $user->mobile,
                'password' => $user->mobile,
                'mac' => $mac,
                'dst' => url('/success'),
            ]);
        }

        // No router URL: just go to success (IP binding handles it)
        return redirect('/success')->with('success', 'Activation signal sent!');
    }

    public function paymentPage()
    {
        return view('payment');
    }
}