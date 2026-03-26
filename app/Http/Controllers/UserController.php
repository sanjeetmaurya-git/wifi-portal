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
