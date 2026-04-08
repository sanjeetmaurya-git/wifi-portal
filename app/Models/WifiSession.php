<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WifiSession extends Model
{
    protected $table = 'wifi_sessions';

    protected $fillable = [
        'user_id',
        'mac_address',
        'ip_address',
        'login_at',
        'logout_at',
        'duration_minutes',
        'device_name',
        'browser',
        'os',
        'expires_at',
        'wifi_plan_id',
        'is_free',
    ];

    //users info session 
    
    public function user()
    {
        return $this->belongsTo(WifiUser::class,'user_id');
    }
}
