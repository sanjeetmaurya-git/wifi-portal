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
            // 🔐 VERIFY PAYMENT (MOST IMPORTANT)
            $api->utility->verifyPaymentSignature([
                'razorpay_order_id' => $request->order_id,
                'razorpay_payment_id' => $request->payment_id,
                'razorpay_signature' => $request->signature
            ]);

            // $transaction = Transaction::where('order_id', $request->order_id)->first();
            $transaction = Transaction::where('order_id', $request->order_id)->where('status', 'created')->first();  //Step 26

            if (!$transaction) {
                return response()->json(['error' => 'Invalid Transaction'], 400);
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



            // ✅ Activate plan with correct expires_at
            WifiSession::create([
                'user_id'          => $user->id,
                'mac_address'      => session('mac'),
                'ip_address'       => session('ip'),
                'login_at'         => now(),
                'duration_minutes' => $plan->duration_minutes,
                'expires_at'       => now()->addMinutes($plan->duration_minutes), // ✅ FIX: was missing!
                'wifi_plan_id'     => $plan->id,
                'is_free'          => false,
            ]);

            // ✅ Add user to MikroTik (must exist BEFORE login attempt)
            $mikrotik->addHotspotUser(
                $user->mobile,
                $user->mobile,
                $plan->profile_name ?? 'default'
            );

            // ✅ Direct Backend Redirect to MikroTik (100% RELIABLE)
            $linkLogin = session('link_login') ?: 'http://' . env('MIKROTIK_HOST', '192.168.88.1') . '/login';
            
            $separator = str_contains($linkLogin, '?') ? '&' : '?';
            $loginUrl = $linkLogin . $separator . 'username=' . urlencode($user->mobile) . '&password=' . urlencode($user->mobile);
            
            return redirect($loginUrl);

            // Fallback: no router in dev mode
            return redirect('/plans')->with('success', 'Payment successful! Your plan is now active.');

        } catch (SignatureVerificationError $e) {

            return response()->json([
                'error' => 'Payment verification failed'
            ], 400);
        }
    }

    // ✅ Payment Page
    public function paymentPage()
    {
        return view('payment');
    }
}