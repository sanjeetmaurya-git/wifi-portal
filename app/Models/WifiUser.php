<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WifiUser extends Model
{
    // protected $fillable = [ 'mobile', 'mac_address', 'ip_address', 'active' ];
    protected $fillable = [
        'mobile',
        'mac_address',
        'ip_address',
        'active',
        'full_name',
        'address',
        'city',
        'district',
        'state',
        'pincode',
        'id_proof',
        'last_verified_at'
    ];
}
