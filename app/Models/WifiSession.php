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
        'parent_session_id',
        'bonus_data_mb',
        'used_mb',
    ];

    protected $casts = [
        'login_at'   => 'datetime',
        'logout_at'  => 'datetime',
        'expires_at' => 'datetime',
        'is_free'    => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(WifiUser::class, 'user_id');
    }

    public function plan()
    {
        return $this->belongsTo(WifiPlan::class, 'wifi_plan_id');
    }

    public function parentSession()
    {
        return $this->belongsTo(WifiSession::class, 'parent_session_id');
    }

    /** Is this session still valid and not logged out? */
    public function isActive(): bool
    {
        return $this->expires_at > now() && is_null($this->logout_at);
    }
}
