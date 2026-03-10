<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OtpRequest;
use Carbon\Carbon;

class AuthController extends Controller
{
    //login page
    public function loginPage(){
        return view('login');
    }
    
    public function sendOtp( Request $request){
        $mobile = $request->mobile;
        $otp = rand(100000,999999);
        OtpRequest::create([
            'mobile' => $mobile,
            'otp_code' => $otp,
            'ip_address' => $request->ip(),
            'expires_at' => Carbon::now()->addMinutes(5)
            ]);
        // session([
        //     'otp'=> $otp,
        //     'mobile'=> $mobile

        // ]);

         echo "Your OTP is: ".$otp;

        return view('verify', compact('mobile'));
    }

    public function verifyOtp( Request $request){
        $otp = OtpRequest::where('mobile',$request->mobile)
        ->where('otp_code',$request->otp)
        ->where('verified',false)
        ->where('expires_at','>',Carbon::now())
        ->first();
        if($otp){
            $otp->update([
                'verified'=>true
                ]);
            return "Login Sucess";
        }
        return "Invalid or Expired OTP";
        // if($request->otp == session('otp')){
        //     return "Login Sucess";
        // }else{
        //     return "Invalid Otp";
        // }
    }
}
