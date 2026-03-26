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
}
