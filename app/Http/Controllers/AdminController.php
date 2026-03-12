<?php

namespace App\Http\Controllers;

use App\Models\WifiUser;
use App\Models\WifiSession;
use App\Models\OtpRequest;
use App\Models\UsageLog;
use App\Services\MikrotikService;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends Controller
{

    public function dashboard()
    {
        $users = WifiUser::count();

        $activeSessions = WifiSession::whereNull('logout_at')->count();

        $otpRequests = OtpRequest::count();

        return view('admin.dashboard', compact(
            'users',
            'activeSessions',
            'otpRequests'
        ));
    }

    public function users()
    {
        $users = WifiUser::latest()->paginate(10);

        return view('admin.users',compact('users'));
    }

    //sessions 
    public function sessions()
    {
        $sessions = WifiSession::latest()->paginate(10);

        return view('admin.sessions',compact('sessions'));
    }

    public function otpLogs()
    {
        $logs = OtpRequest::latest()->paginate(10);

        return view('admin.otp_logs',compact('logs'));
    }

    // data analytics 
    public function analyticsData()
    {
        $activeUsers = \App\Models\WifiSession::whereNull('logout_at')->count();

        $sessionsToday = \App\Models\WifiSession::whereDate('login_at', today())->count();

        $otpToday = \App\Models\OtpRequest::whereDate('created_at', today())->count();

        $users = \App\Models\WifiUser::count();

        return response()->json([
            'active_users'=>$activeUsers,
            'sessions_today'=>$sessionsToday,
            'otp_today'=>$otpToday,
            'total_users'=>$users
        ]);
    }

    //Active user sessions 
    public function activeSessions(){
        $sessions = WifiSession::whereNull('logout_at')
        ->with('user')
        ->latest()
        ->get();

        return response()->json($sessions);
    }

    //disconnected User 
    public function disconnectUser(Request $request)
    {
        $session = WifiSession::find($request->session_id);
        if(!$session){
            return response()->json(['success'=>false]);
        }
        $session->logout_at = now();
        $session->save();

        try{
            $mikrotik = new MikrotikService();
            $mikrotik->removeHotspotUser(
                $session->user->mobile
            );
        }catch(\Exception $e){

        // router not connected yet

        }

        return response()->json([
            'success'=>true
        ]);
    }

    //data usage 
    public function usageStats()
    {
        $stats = UsageLog::with('user')
        ->latest()
        ->paginate(20);
        return view('admin.usage',compact('stats'));
    }

}