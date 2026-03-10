<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WifiUser extends Model
{
    protected $fillable = [ 'mobile', 'mac_address', 'ip_address', 'active' ];
}
