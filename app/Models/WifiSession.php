<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WifiSession extends Model
{
    protected $fillable = ['user_id', 'mac_address', 'ip_address', 'login_at'];
}
