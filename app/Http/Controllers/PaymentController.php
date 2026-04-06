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

class PaymentController extends Controller
{
    // ✅ Create Order
    public function createOrder(Request $request)
    {
        if (!session('mobile')) {
            return redirect('/login')->with('error', 'Please login to buy a plan.');
        }

        $plan = WifiPlan::findOrFail($request->plan_id);
        $user = WifiUser::where('mobile', session('mobile'))->first();

        // $api = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));
        $api = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));

        $order = $api->order->create([
            'receipt' => uniqid(),
            'amount' => $plan->price * 100,
            'currency' => 'INR'
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'wifi_plan_id' => $plan->id,
            'order_id' => $order['id'],
            'amount' => $plan->price,
            'status' => 'created'
        ]);

        session([
            'order_id' => $order['id'],
            'plan_id' => $plan->id,
            'amount' => $plan->price
        ]);

        return redirect('/payment-page');
    }

    // ✅ Payment Success (SECURE VERSION)
    public function paymentSuccess(Request $request, MikrotikService $mikrotik)
    {
        // $api = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));
        $api = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));

        try {
            // 🔐 VERIFY PAYMENT SIGNATURE
            $razorpayOrderId = $request->input('razorpay_order_id');
            $razorpayPaymentId = $request->input('razorpay_payment_id');
            $razorpaySignature = $request->input('razorpay_signature');

            if (!$razorpaySignature) {
                return response()->json(['error' => 'Razorpay Signature is missing!'], 400);
            }

            $api->utility->verifyPaymentSignature([
                'razorpay_order_id' => $razorpayOrderId,
                'razorpay_payment_id' => $razorpayPaymentId,
                'razorpay_signature' => $razorpaySignature
            ]);

            $transaction = Transaction::where('order_id', $razorpayOrderId)->where('status', 'created')->first();

            if (!$transaction) {
                return response()->json(['error' => 'Invalid or Duplicate Transaction'], 400);
            }

            //verify transaction ammunt 
            if ($transaction->amount != session('amount')) {
                return response()->json(['error' => 'Amount mismatch'], 400);
            }

            // ✅ Prevent duplicate payment
            if ($transaction->status === 'paid') {
                return response()->json(['error' => 'Already Paid']);
            }

            // get currnt plan of user 
            $user = WifiUser::find($transaction->user_id);
            $plan = WifiPlan::find($transaction->wifi_plan_id);

            // ✅ Update transaction
            $transaction->update([
                'payment_id' => $request->payment_id,
                'status' => 'paid',
                'expires_at' => now()->addMinutes($plan->duration_minutes)
            ]);

            // 🔥 RECOVER SESSION DETAILS (If lost)
            $mac = session('mac') ?? $user->mac_address;
            $ip = session('ip') ?? $user->ip_address;

            // ✅ Activate plan session
            WifiSession::create([
                'user_id'          => $user->id,
                'mac_address'      => $mac,
                'ip_address'       => $ip,
                'login_at'         => now(),
                'duration_minutes' => $plan->duration_minutes,
                'expires_at'       => now()->addMinutes($plan->duration_minutes),
                'wifi_plan_id'     => $plan->id,
                'is_free'          => false,
            ]);

            // ✅ Push to MikroTik with Plan Limits
            try {
                $uptimeLimit = $plan->duration_minutes . 'm';
                $bytesLimit  = $plan->limit_bytes ? ($plan->limit_bytes . 'M') : null;

                $mikrotik->addHotspotUser(
                    $user->mobile, 
                    $user->mobile, 
                    $plan->profile_name ?? 'default',
                    $uptimeLimit,
                    $bytesLimit,
                    null
                );
            } catch (\Exception $e) { /* Already exists */ }

            // ✅ THE FINAL HANDSHAKE (Physical Relay)
            $linkLogin = session('link_login') ?: 'http://' . env('MIKROTIK_HOST', '192.168.88.1') . '/login';
            
            // Generate a hidden form that auto-submits to authorize the device
            return "
                <html>
                <body onload='document.forms[0].submit()'>
                    <form method='POST' action='{$linkLogin}'>
                        <input type='hidden' name='username' value='{$user->mobile}'>
                        <input type='hidden' name='password' value='{$user->mobile}'>
                        <input type='hidden' name='dst' value='http://www.google.com'>
                    </form>
                    <p style='text-align:center; font-family:sans-serif; margin-top:100px;'>
                        Enabling Internet Access... Please wait.
                    </p>
                </body>
                </html>
            ";

        } catch (SignatureVerificationError $e) {
            return response()->json(['error' => 'Payment verification failed'], 400);
        }
    }

    // ✅ Payment Page
    public function paymentPage()
    {
        return view('payment');
    }
}