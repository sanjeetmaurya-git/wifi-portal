<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsageLog extends Model
{
    protected $fillable = [
        'user_id',
        'download_bytes',
        'upload_bytes',
        'recorded_at'
    ];
}
