<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpRequest extends Model
{
    protected $fillable = ['mobile','otp_code', 'ip_address','expires_at'];
}
