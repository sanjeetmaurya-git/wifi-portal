<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;
use App\Models\Transaction;
use App\Models\WifiPlan;
use App\Models\WifiUser;
use App\Models\WifiSession;

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
    public function paymentSuccess(Request $request)
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

            $transaction = Transaction::where('order_id', $request->order_id)->first();

            if (!$transaction) {
                return response()->json(['error' => 'Invalid Order'], 400);
            }

            // ✅ Prevent duplicate payment
            if ($transaction->status === 'paid') {
                return response()->json(['error' => 'Already Paid']);
            }

            // ✅ Update transaction
            $transaction->update([
                'payment_id' => $request->payment_id,
                'status' => 'paid'
            ]);

            $user = WifiUser::find($transaction->user_id);
            $plan = WifiPlan::find($transaction->wifi_plan_id);

            // ✅ Activate plan
            WifiSession::create([
                'user_id' => $user->id,
                'mac_address' => session('mac'),
                'ip_address' => session('ip'),
                'login_at' => now(),
                'duration_minutes' => $plan->duration_minutes,
                'wifi_plan_id' => $plan->id,
                'is_free' => false
            ]);

            return response()->json(['success' => true]);

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