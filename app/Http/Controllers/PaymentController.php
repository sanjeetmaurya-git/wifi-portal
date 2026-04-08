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

        // 💳 PAID PLAN: Create Razorpay Order
        $api = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));

        $order = $api->order->create([
            'receipt'  => 'rcpt_' . $user->id . '_' . time(),
            'amount'   => (int)($plan->price * 100),
            'currency' => 'INR'
        ]);

        Transaction::create([
            'user_id'      => $user->id,
            'wifi_plan_id' => $plan->id,
            'order_id'     => $order['id'],
            'amount'       => $plan->price,
            'status'       => 'created'
        ]);

        // Store everything needed for verification in the session
        session([
            'order_id'  => $order['id'],
            'plan_id'   => $plan->id,
            'amount'    => $plan->price,
            'user_id'   => $user->id,  // ← store so we can recover if session expires
        ]);

        return redirect('/payment-page');
    }

    // ✅ Payment Success Callback from Razorpay JS (AJAX POST)
    public function paymentSuccess(Request $request, MikrotikService $mikrotik)
    {
        $api = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));

        try {
            $razorpayOrderId   = $request->input('razorpay_order_id');
            $razorpayPaymentId = $request->input('razorpay_payment_id');
            $razorpaySignature = $request->input('razorpay_signature');

            if (!$razorpaySignature) {
                return response()->json(['error' => 'Payment signature missing.'], 400);
            }

            // 🔐 Verify payment signature with Razorpay
            $api->utility->verifyPaymentSignature([
                'razorpay_order_id'   => $razorpayOrderId,
                'razorpay_payment_id' => $razorpayPaymentId,
                'razorpay_signature'  => $razorpaySignature
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

            // ✅ Mark transaction as paid
            $transaction->update([
                'payment_id' => $razorpayPaymentId,
                'status'     => 'paid',
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
     * Supports plan QUEUING: if user already has an active plan, the new plan
     * is queued and auto-activated when the current one expires.
     */
    private function activatePlan($user, $plan, $request, bool $isFree)
    {
        $mac = session('mac') ?? $user->mac_address ?? 'unknown';
        $ip  = session('ip')  ?? $user->ip_address  ?? $request->ip();

        // 📦 PLAN QUEUING: Check if user already has an active plan
        $activeSession = WifiSession::where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->whereNull('logout_at')
            ->latest()
            ->first();

        if ($activeSession) {
            // ✅ User has an active plan. QUEUE the new plan.
            // New plan starts when current one expires.
            $startAt = $activeSession->expires_at;
            WifiSession::create([
                'user_id'          => $user->id,
                'mac_address'      => $mac,
                'ip_address'       => $ip,
                'login_at'         => $startAt,
                'duration_minutes' => $plan->duration_minutes,
                'expires_at'       => Carbon::parse($startAt)->addMinutes($plan->duration_minutes),
                'wifi_plan_id'     => $plan->id,
                'is_free'          => $isFree,
            ]);
            Log::info('[Plan] Queued plan for ' . $user->mobile . '. Starts at: ' . $startAt);

            // No MikroTik push needed yet — current plan still active.
            // Redirect to success page to show current session status.
            session(['mobile' => $user->mobile]);
            if ($request->expectsJson() || $request->isXmlHttpRequest()) {
                return response()->json(['redirect' => url('/success?queued=1')]);
            }
            return redirect('/success?queued=1');
        }

        // 🆕 No active plan — activate immediately
        WifiSession::create([
            'user_id'          => $user->id,
            'mac_address'      => $mac,
            'ip_address'       => $ip,
            'login_at'         => now(),
            'duration_minutes' => $plan->duration_minutes,
            'expires_at'       => now()->addMinutes($plan->duration_minutes),
            'wifi_plan_id'     => $plan->id,
            'is_free'          => $isFree,
        ]);

        // 📡 Push limits to MikroTik
        try {
            $mikrotik    = new MikrotikService();
            $profileName = $plan->profile_name ?: 'plan_' . $plan->id;
            $uptimeLimit = $plan->duration_minutes . 'm';
            $bytesLimit  = $plan->limit_bytes ? ($plan->limit_bytes . 'M') : null;
            $rateLimit   = ($plan->upload_limit && $plan->download_limit)
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
            Log::info('[MikroTik] Plan pushed for ' . $user->mobile . ' Profile:' . $profileName);
        } catch (\Exception $e) {
            Log::error('[MikroTik] Failed to push plan for ' . $user->mobile . ': ' . $e->getMessage());
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
        $mac          = session('mac') ?? $user->mac_address ?? '';

        // Always persist user in session for /success and /activate-internet
        session(['mobile' => $user->mobile]);

        if (!$rawLinkLogin) {
            // No MikroTik link — portal accessed directly, not via router redirect
            if ($request->expectsJson() || $request->isXmlHttpRequest()) {
                return response()->json(['redirect' => url('/success')]);
            }
            return redirect('/success');
        }

        // Replace DNS hostname with actual router IP for universal device compatibility
        $routerIp  = env('MIKROTIK_HOST', '192.168.88.1');
        $linkLogin = str_replace(['wifi.local', 'mywifi.net'], $routerIp, $rawLinkLogin);

        if ($request->expectsJson() || $request->isXmlHttpRequest()) {
            // ✅ AJAX (Razorpay paid plan):
            // Store connect params in session, tell JS to navigate to /activate-internet.
            // This triggers a fresh full-page load → IIFE auto-submits form reliably.
            session([
                'activate_link_login' => $linkLogin,
                'activate_mac'        => $mac,
            ]);
            return response()->json(['redirect' => url('/activate-internet')]);
        }

        // Free plan (normal HTML form POST) — return the view directly
        return view('mikrotik-login', [
            'link_login' => $linkLogin,
            'username'   => $user->mobile,
            'password'   => $user->mobile,
            'mac'        => $mac,
            'dst'        => url('/success'),
        ]);
    }

    // ✅ GET /activate-internet — Fresh page that auto-POSTs to MikroTik
    // Called after Razorpay success redirect from JS.
    public function activateInternet(Request $request)
    {
        // 🔍 Retrieve data from session (stored during callback)
        $mobile    = session('mobile');
        $linkLogin = session('activate_link_login') ?? session('link_login');
        $mac       = session('activate_mac') ?? session('mac') ?? '';

        if (!$mobile) {
             // Fallback: Check if we have it in a cookie or just redirect to login
             return redirect('/login')->with('error', 'Session lost. Please try to connect again.');
        }

        $user = WifiUser::where('mobile', $mobile)->first();
        if (!$user) {
            return redirect('/login')->with('error', 'User not found.');
        }

        // If we don't have a link_login, we can't do the handshake.
        // But we can try the IP fallback instead of just failing.
        if (!$linkLogin) {
            $routerIp  = env('MIKROTIK_HOST', '192.168.88.1');
            $linkLogin = "http://{$routerIp}/login";
        }

        // 🧬 Double-check: Replace 'wifi.local' DNS with IP to ensure it works even if DNS isn't ready.
        // We keep a fallback to the original link if the IP one fails (handled by the User manually).
        $routerIp     = env('MIKROTIK_HOST', '192.168.88.1');
        $finalLoginUrl = str_replace(['wifi.local', 'mywifi.net'], $routerIp, $linkLogin);

        Log::info("[Handshake] Ready for $mobile at $finalLoginUrl");

        return view('mikrotik-login', [
            'link_login' => $finalLoginUrl,
            'username'   => $user->mobile,
            'password'   => $user->mobile,
            'mac'        => $mac,
            'dst'        => url('/success'),
        ]);
    }

    // ✅ Payment Page View
    public function paymentPage()
    {
        return view('payment');
    }
}